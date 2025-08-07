// 🛠️ CONFIGURATION
const SLACK_WEBHOOK_URL = '#'; // Replace with your webhook
const MASTER_FOLDER_ID = '#'; // Replace with your master folder ID

function onFormSubmit(e) {
  const responses = e.namedValues || {};
  const timestamp = e.values?.[0] || new Date().toISOString();

  Logger.log("Available fields: " + JSON.stringify(Object.keys(responses)));

  const email = responses["Email Address"]?.[0] || "Missing Email";
  const campaignName = responses["Campaign Name"]?.[0] || "Missing Campaign Name";
  const pointOfContact = responses["Point of contact"]?.[0] || "Missing Contact";
  const links = responses["Links"]?.[0] || "None";
  const publishDateStr = responses["Publish Date"]?.[0] || "Missing Publish Date";
  const platforms = responses["Platforms"]?.[0] || "Missing Platforms";
  const otherNotes = responses["Other notes?"]?.[0] || "None";
  const imagesField = "Images";

  // 1. Create folder and doc
  const folderUrl = createSubmissionFolder(campaignName, timestamp);
  const folderId = extractFolderId(folderUrl);
  const destinationFolder = DriveApp.getFolderById(folderId);

  // 2. Move and rename images
  moveAndRenameFiles([imagesField], responses, campaignName, destinationFolder);

  // 3. Create a summary Google Doc with all info
  createSummaryDoc(destinationFolder, campaignName, {
    email,
    campaignName,
    pointOfContact,
    links,
    publishDateStr,
    platforms,
    otherNotes
  });

  // 4. Send formatted Slack message
  sendToSlack({
  text:
    `*🆕 New Campaign Submission*\n\n` +
    `*Campaign:* *${campaignName}*\n` +
    `👤 *Point of Contact:* ${pointOfContact}\n` +
    `──────────────\n\n` +
    `📅 *Publish Date:* ${publishDateStr}\n` +
    `📣 *Platforms:* ${platforms}\n` +
    `🔗 *Links:* ${links}\n` +
    `🗒 *Notes:* ${otherNotes}\n\n` +
    `📁 *Assets Folder:* ${folderUrl}\n\n`
});



  // 5. Schedule reminder 1 day before publish date
  const publishDate = new Date(publishDateStr);
  const reminderDate = new Date(publishDate);
  reminderDate.setDate(publishDate.getDate() - 1); // One day before
  scheduleReminder(campaignName, publishDateStr, reminderDate);
}

// 📁 Create a folder for each campaign
function createSubmissionFolder(campaignName, timestamp) {
  const parentFolder = DriveApp.getFolderById(MASTER_FOLDER_ID);
  const folderName = `${campaignName} - ${timestamp}`;
  const newFolder = parentFolder.createFolder(folderName);
  return newFolder.getUrl();
}

// 🔁 Extract folder ID from URL
function extractFolderId(url) {
  const match = url.match(/[-\w]{25,}/);
  return match ? match[0] : null;
}

// 📦 Move uploaded image files into the submission folder and rename them
function moveAndRenameFiles(uploadFields, responses, campaignName, destinationFolder) {
  uploadFields.forEach(field => {
    const fileUrls = responses[field]?.[0]?.split(',') || [];
    fileUrls.forEach((url, index) => {
      try {
        const fileId = url.match(/[-\w]{25,}/)[0];
        const file = DriveApp.getFileById(fileId);
        const ext = file.getName().split('.').pop();
        const newName = `${field} - ${campaignName} - ${index + 1}.${ext}`;
        file.setName(newName);
        destinationFolder.addFile(file);

        const parents = file.getParents();
        while (parents.hasNext()) {
          file.removeFromFolder(parents.next());
        }
      } catch (err) {
        Logger.log(`File processing error in field "${field}": ${err}`);
      }
    });
  });
}

// 📝 Create a Google Doc with submission details
function createSummaryDoc(folder, campaignName, data) {
  const doc = DocumentApp.create(`${campaignName} Summary`);
  const body = doc.getBody();

  body.appendParagraph(`📣 Campaign Submission Summary`).setHeading(DocumentApp.ParagraphHeading.HEADING1);
  body.appendParagraph(`Campaign Name: ${data.campaignName}`);
  body.appendParagraph(`Email: ${data.email}`);
  body.appendParagraph(`Point of Contact: ${data.pointOfContact}`);
  body.appendParagraph(`Publish Date: ${data.publishDateStr}`);
  body.appendParagraph(`Platforms: ${data.platforms}`);
  body.appendParagraph(`Links: ${data.links}`);
  body.appendParagraph(`Other Notes: ${data.otherNotes}`);

  doc.saveAndClose();

  const file = DriveApp.getFileById(doc.getId());
  const originalFolder = file.getParents().hasNext() ? file.getParents().next() : null;

  if (originalFolder) {
    try {
      originalFolder.removeFile(file); // Removes from default "My Drive"
    } catch (err) {
      Logger.log("Could not remove file from original folder: " + err);
    }
  }

  folder.addFile(file);
}

// 📬 Send Slack message with logging
function sendToSlack(message) {
  try {
    const response = UrlFetchApp.fetch(SLACK_WEBHOOK_URL, {
      method: 'post',
      contentType: 'application/json',
      payload: JSON.stringify(message),
    });
    Logger.log("Slack response: " + response.getContentText());
  } catch (err) {
    Logger.log("Slack error: " + err);
  }
}

// ⏰ Schedule reminder for 1 day before publish date
function scheduleReminder(campaignName, publishDateStr, reminderDate) {
  ScriptApp.newTrigger('sendReminder')
    .timeBased()
    .at(new Date(reminderDate.getFullYear(), reminderDate.getMonth(), reminderDate.getDate(), 9)) // 9 AM
    .create();

  const props = PropertiesService.getScriptProperties();
  props.setProperty('reminder_' + reminderDate.toDateString(), JSON.stringify({ campaignName, publishDateStr }));
}

// 🔔 Send reminder Slack message
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
