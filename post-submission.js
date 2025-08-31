/**** 🛠️ CONFIGURATION ****/
const SLACK_WEBHOOK_URL = 'https://hooks.slack.com/services/REPLACE/ME/PLEASE'; // ← your Slack Incoming Webhook URL
const MASTER_FOLDER_ID  = 'REPLACE_MASTER_FOLDER_ID';                            // ← your Drive folder ID for campaign subfolders

/**
 * Exact labels from your current form, plus a few sensible variants.
 * If you later rename a question, just add the new label to the right array.
 */
const FIELD_MAP = {
  // Optional — not in current form, but kept for future-proofing:
  email: [
    "Email Address", "Email", "Your Email", "Contact Email"
  ],

  campaignName: [
    "Campaign Name"
  ],
  pointOfContact: [
    "Point of Contact Name", "Point of contact", "POC", "Primary Contact", "Owner", "Contact Person"
  ],

  // We’ll merge these two into one links string
  linksPrimary: [
    "Applicable Links", "Links", "Reference Links", "Asset Links", "Drive / URLs", "Relevant Links"
  ],
  linksEventbrite: [
    "Eventbrite Embed", "Eventbrite Link", "Eventbrite URL"
  ],

  publishDate: [
    "What day should this be posted?", "Publish Date", "Target Publish Date", "Launch Date", "Go-Live Date", "Post Date"
  ],
  platforms: [
    "Which platforms?", "Platforms", "Where to Post", "Channels", "Distribution Platforms"
  ],
  otherNotes: [
    "Other notes", "Other notes?", "Notes", "Additional Notes", "Anything else?"
  ],

  // File upload field(s)
  imageUploads: [
    "Images and Video", "Images", "Image Uploads", "Upload Images", "Assets", "Upload Assets", "Photos", "Videos", "Media"
  ]
};

/**** 🔎 UTILITIES ****/
const norm = s => String(s || "").trim().toLowerCase();

/** Lowercase, hyphen-separated slug */
function slug(s) {
  return String(s || "")
    .normalize("NFKD")
    .replace(/[\u0300-\u036f]/g, "")
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function findLabelKey(responses, variants) {
  const keys = Object.keys(responses || {});
  const want = variants.map(norm);
  for (const k of keys) {
    const nk = norm(k);
    if (want.includes(nk)) return k;
  }
  // also try loose contains (e.g., "Platforms (choose all that apply)")
  for (const k of keys) {
    const nk = norm(k);
    if (want.some(v => nk.includes(v))) return k;
  }
  return null;
}

function valueFrom(responses, key, fallback = "") {
  if (!key || !(key in responses)) return fallback;
  const v = responses[key];
  if (Array.isArray(v)) {
    if (v.length === 1) return String(v[0] ?? fallback);
    return v.filter(x => x != null && String(x).trim() !== "").join(", ");
  }
  return String(v ?? fallback);
}

function fileUrlsFrom(responses, key) {
  const raw = valueFrom(responses, key, "");
  if (!raw) return [];
  return raw.split(",").map(s => s.trim()).filter(Boolean);
}

/**** 🚀 MAIN ENTRY (installable trigger) ****/
function onFormSubmit(e) {
  const startedAt = new Date();
  try {
    const responses = e?.namedValues || {};
    const timestamp = e?.values?.[0] || new Date().toISOString();

    Logger.log("onFormSubmit fired. Keys: " + JSON.stringify(Object.keys(responses)));

    // Resolve actual keys present for each field
    const kEmail         = findLabelKey(responses, FIELD_MAP.email);
    const kCampaign      = findLabelKey(responses, FIELD_MAP.campaignName);
    const kPOC           = findLabelKey(responses, FIELD_MAP.pointOfContact);
    const kLinks1        = findLabelKey(responses, FIELD_MAP.linksPrimary);
    const kLinks2        = findLabelKey(responses, FIELD_MAP.linksEventbrite);
    const kPublish       = findLabelKey(responses, FIELD_MAP.publishDate);
    const kPlatforms     = findLabelKey(responses, FIELD_MAP.platforms);
    const kNotes         = findLabelKey(responses, FIELD_MAP.otherNotes);
    const kImageUploads  = findLabelKey(responses, FIELD_MAP.imageUploads);

    Logger.log("Detected labels: " + JSON.stringify({
      email: kEmail, campaignName: kCampaign, pointOfContact: kPOC,
      linksPrimary: kLinks1, eventbrite: kLinks2,
      publishDate: kPublish, platforms: kPlatforms,
      otherNotes: kNotes, imageUploads: kImageUploads
    }, null, 2));

    // Pull normalized values
    const email          = valueFrom(responses, kEmail, "N/A");
    const campaignName   = valueFrom(responses, kCampaign, "Missing Campaign Name");
    const pointOfContact = valueFrom(responses, kPOC, "Missing Contact");

    const linksPrimary   = valueFrom(responses, kLinks1, "");
    const eventbrite     = valueFrom(responses, kLinks2, "");
    const links = [linksPrimary, eventbrite].map(s => s && s.trim()).filter(Boolean).join("\n");

    const publishDateStr = valueFrom(responses, kPublish, "Missing Publish Date");
    const platforms      = valueFrom(responses, kPlatforms, "Missing Platforms");
    const otherNotes     = valueFrom(responses, kNotes, "None");

    // Create destination folder
    const folderUrl = createSubmissionFolder(campaignName, timestamp);
    const folderId = extractFolderId(folderUrl);
    const destinationFolder = DriveApp.getFolderById(folderId);

    // Move & rename uploaded files (if any)
    if (kImageUploads) {
      moveAndRenameFiles([kImageUploads], responses, campaignName, destinationFolder);
    } else {
      Logger.log("No matching file-upload field found. If you renamed it, add the label to FIELD_MAP.imageUploads.");
    }

    // Create summary doc
    createSummaryDoc(destinationFolder, campaignName, {
      email, campaignName, pointOfContact, links, publishDateStr, platforms, otherNotes
    });

    // Send Slack notification (success)
    sendToSlack({
      text:
        `*🆕 New Campaign Submission*\n\n` +
        `*Campaign:* *${campaignName}*\n` +
        `👤 *Point of Contact:* ${pointOfContact}\n` +
        `──────────────\n\n` +
        `📅 *Publish Date:* ${publishDateStr}\n` +
        `📣 *Platforms:* ${platforms}\n` +
        `🔗 *Links:*\n${links || "None"}\n\n` +
        `🗒 *Notes:* ${otherNotes}\n\n` +
        `📁 *Assets Folder:* ${folderUrl}\n\n` +
        `_Processed in ${((new Date()) - startedAt)} ms_`
    });

    // Schedule reminder 1 day before publish date (09:00 local)
    const publishDate = parseFlexibleDate(publishDateStr);
    if (publishDate) {
      const reminderDate = new Date(publishDate);
      reminderDate.setDate(publishDate.getDate() - 1);
      scheduleReminder(campaignName, publishDateStr, reminderDate);
    } else {
      Logger.log(`Could not parse publish date from "${publishDateStr}". Reminder not scheduled.`);
    }

  } catch (err) {
    Logger.log("onFormSubmit error: " + (err && err.stack || err));
    // Best-effort Slack failure message, so issues aren't silent
    try {
      sendToSlack({
        text: `:warning: *Form submission failed in Apps Script*\n\`\`\`${String(err)}\n${(err && err.stack) || ""}\n\`\`\``
      });
    } catch (_) {
      // If even Slack fails, rely on Executions log
    }
  }
}

/**** 📁 Create a folder for each campaign ****/
function createSubmissionFolder(campaignName, timestamp) {
  const parentFolder = DriveApp.getFolderById(MASTER_FOLDER_ID);
  const safeName = (campaignName || "Untitled").replace(/[\\/:*?"<>|]/g, " ");
  const folderName = `${safeName} - ${timestamp}`;
  const newFolder = parentFolder.createFolder(folderName);
  return newFolder.getUrl();
}

/**** 🔁 Extract folder ID from URL ****/
function extractFolderId(url) {
  const match = url && url.match(/[-\w]{25,}/);
  return match ? match[0] : null;
}

/**** 📦 Move uploaded files and rename them
 * New name: <original-file-name>-<campaign-name>-<seq>.<ext> (all lowercase, hyphen-separated)
 ****/
function moveAndRenameFiles(uploadFields, responses, campaignName, destinationFolder) {
  const campaignSlug = slug(campaignName || "untitled");

  uploadFields.forEach(fieldKey => {
    const urls = fileUrlsFrom(responses, fieldKey);

    urls.forEach((url, index) => {
      try {
        const fileIdMatch = url.match(/[-\w]{25,}/);
        if (!fileIdMatch) throw new Error("No file ID in URL: " + url);

        const fileId = fileIdMatch[0];
        const file = DriveApp.getFileById(fileId);

        // Original name pieces
        const originalName = file.getName();                // e.g., "My Photo 1.JPG"
        const dot = originalName.lastIndexOf(".");
        const base = dot > -1 ? originalName.slice(0, dot) : originalName;
        const ext  = dot > -1 ? originalName.slice(dot + 1) : "";  // no dot

        // Build new name
        const seq = String(index + 1).padStart(2, "0");
        const newName =
          `${slug(base)}-${campaignSlug}-${seq}` + (ext ? `.${String(ext).toLowerCase()}` : "");

        // Rename + move
        file.setName(newName);
        destinationFolder.addFile(file);

        // Remove from any previous parent folders
        const parents = file.getParents();
        while (parents.hasNext()) {
          file.removeFromFolder(parents.next());
        }

        Logger.log(`Renamed "${originalName}" → "${newName}"`);

      } catch (err) {
        Logger.log(`File processing error in field "${fieldKey}": ${err}`);
      }
    });
  });
}

/**** 📝 Create a Google Doc with submission details ****/
function createSummaryDoc(folder, campaignName, data) {
  const doc = DocumentApp.create(`${data.campaignName} Summary`);
  const body = doc.getBody();

  body.appendParagraph(`📣 Campaign Submission Summary`).setHeading(DocumentApp.ParagraphHeading.HEADING1);
  body.appendParagraph(`Campaign Name: ${data.campaignName}`);
  body.appendParagraph(`Point of Contact: ${data.pointOfContact}`);
  body.appendParagraph(`Publish Date: ${data.publishDateStr}`);
  body.appendParagraph(`Platforms: ${data.platforms}`);
  body.appendParagraph(`Links:\n${data.links || "None"}`);
  body.appendParagraph(`Other Notes: ${data.otherNotes}`);

  doc.saveAndClose();

  const file = DriveApp.getFileById(doc.getId());
  const parents = file.getParents();
  if (parents.hasNext()) {
    try {
      const original = parents.next();
      original.removeFile(file);
    } catch (err) {
      Logger.log("Could not remove file from original folder: " + err);
    }
  }
  folder.addFile(file);
}

/**** 📬 Slack: strict response handling ****/
function sendToSlack(message) {
  const resp = UrlFetchApp.fetch(SLACK_WEBHOOK_URL, {
    method: 'post',
    contentType: 'application/json',
    payload: JSON.stringify(message),
    muteHttpExceptions: true,
  });
  const code = resp.getResponseCode();
  const text = resp.getContentText();
  Logger.log("Slack response: " + code + " " + text);
  if (code !== 200) throw new Error(`Slack error ${code}: ${text}`);
}

/**** 🗓 Parse flexible date strings safely ****/
function parseFlexibleDate(s) {
  if (!s) return null;
  const d1 = new Date(s);
  if (!isNaN(d1)) return d1;
  const m = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{2,4})$/);
  if (m) {
    const mm = Number(m[1]) - 1;
    const dd = Number(m[2]);
    const yyyy = Number(m[3].length === 2 ? "20" + m[3] : m[3]);
    const d2 = new Date(yyyy, mm, dd);
    if (!isNaN(d2)) return d2;
  }
  return null;
}

/**** ⏰ Schedule reminder for 1 day before publish date ****/
function scheduleReminder(campaignName, publishDateStr, reminderDate) {
  ScriptApp.newTrigger('sendReminder')
    .timeBased()
    .at(new Date(reminderDate.getFullYear(), reminderDate.getMonth(), reminderDate.getDate(), 9)) // 9 AM local
    .create();

  const props = PropertiesService.getScriptProperties();
  props.setProperty('reminder_' + reminderDate.toDateString(), JSON.stringify({ campaignName, publishDateStr }));
}

/**** 🔔 Send reminder Slack message ****/
function sendReminder() {
  const todayKey = 'reminder_' + new Date().toDateString();
  const props = PropertiesService.getScriptProperties();
  const data = props.getProperty(todayKey);
  if (!data) return;

  const { campaignName, publishDateStr } = JSON.parse(data);
  sendToSlack({
    text: `🔔 *Reminder: ${campaignName}* is scheduled to publish tomorrow (*${publishDateStr}*).`
  });

  props.deleteProperty(todayKey);
}

/**** ✅ Helpers for setup & testing ****/
// 1) Run this once to verify Slack + authorize scopes
function testSlack() {
  sendToSlack({ text: ":white_check_mark: Webhook test from Apps Script." });
}

// 2a) If your script is BOUND TO THE FORM, run this ONCE to add the installable trigger
function installTriggerForForm(formId) {
  if (!formId) throw new Error("Pass the Form ID (from its URL) to installTriggerForForm(formId).");
  ScriptApp.newTrigger('onFormSubmit').forForm(formId).onFormSubmit().create();
}

// 2b) If your script is BOUND TO THE RESPONSE SHEET, run this ONCE to add the installable trigger
function installTriggerForSpreadsheet(spreadsheetId) {
  if (!spreadsheetId) throw new Error("Pass the Spreadsheet ID (from its URL) to installTriggerForSpreadsheet(spreadsheetId).");
  ScriptApp.newTrigger('onFormSubmit').forSpreadsheet(spreadsheetId).onFormSubmit().create();
}
