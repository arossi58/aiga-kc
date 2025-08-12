/**** 🛠️ CONFIGURATION ****/
const SLACK_WEBHOOK_URL = '#';        // ← your Slack incoming webhook
const MASTER_FOLDER_ID  = '#';        // ← your master folder ID

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

/**** 🚀 MAIN ENTRY ****/
function onFormSubmit(e) {
  const responses = e?.namedValues || {};
  const timestamp = e?.values?.[0] || new Date().toISOString();

  Logger.log("Available fields: " + JSON.stringify(Object.keys(responses)));

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
  const links = [linksPrimary, eventbrite]
    .map(s => s && s.trim())
    .filter(Boolean)
    .join("\n");

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
    Logger.log("No matching file-upload field found. Add the label to FIELD_MAP.imageUploads if you rename it.");
  }

  // Create summary doc
  createSummaryDoc(destinationFolder, campaignName, {
    email,
    campaignName,
    pointOfContact,
    links,
    publishDateStr,
    platforms,
    otherNotes
  });

  // Send Slack notification
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
      `📁 *Assets Folder:* ${folderUrl}\n\n`
  });

  // Schedule reminder 1 day before publish date (09:00 in script's timezone)
  const publishDate = parseFlexibleDate(publishDateStr);
  if (publishDate) {
    const reminderDate = new Date(publishDate);
    reminderDate.setDate(publishDate.getDate() - 1);
    scheduleReminder(campaignName, publishDateStr, reminderDate);
  } else {
    Logger.log(`Could not parse publish date from "${publishDateStr}". Reminder not scheduled.`);
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

/**** 📦 Move uploaded files into the submission folder and rename them ****/
function moveAndRenameFiles(uploadFields, responses, campaignName, destinationFolder) {
  uploadFields.forEach(fieldKey => {
    const urls = fileUrlsFrom(responses, fieldKey);
    urls.forEach((url, index) => {
      try {
        const fileIdMatch = url.match(/[-\w]{25,}/);
        if (!fileIdMatch) throw new Error("No file ID in URL: " + url);
        const fileId = fileIdMatch[0];
        const file = DriveApp.getFileById(fileId);
        const name = file.getName();
        const ext = name.includes(".") ? name.split(".").pop() : "";
        const base = "Images and Video"; // use your form’s label for cleaner names
        const newName = `${base} - ${campaignName} - ${String(index + 1).padStart(2, "0")}${ext ? "." + ext : ""}`;
        file.setName(newName);
        destinationFolder.addFile(file);

        const parents = file.getParents();
        while (parents.hasNext()) {
          file.removeFromFolder(parents.next());
        }
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

/**** 📬 Send Slack message ****/
function sendToSlack(message) {
  try {
    const response = UrlFetchApp.fetch(SLACK_WEBHOOK_URL, {
      method: 'post',
      contentType: 'application/json',
      payload: JSON.stringify(message),
      muteHttpExceptions: true,
    });
    Logger.log("Slack response: " + response.getResponseCode() + " " + response.getContentText());
  } catch (err) {
    Logger.log("Slack error: " + err);
  }
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
    .at(new Date(reminderDate.getFullYear(), reminderDate.getMonth(), reminderDate.getDate(), 9)) // 9 AM
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
