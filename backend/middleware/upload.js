const multer = require("multer");
const path = require("path");
const fs = require("fs");

// Create upload directory if it doesn't exist
const uploadDir = process.env.UPLOAD_PATH || "./uploads";
const complaintsDir = path.join(uploadDir, "complaints");

if (!fs.existsSync(complaintsDir)) {
  fs.mkdirSync(complaintsDir, { recursive: true });
}

// Configure multer storage
const storage = multer.diskStorage({
  destination: function (req, file, cb) {
    cb(null, complaintsDir);
  },
  filename: function (req, file, cb) {
    // Generate unique filename: timestamp-originalname
    const sanitizedName = file.originalname.replace(/[^a-zA-Z0-9.-]/g, "_");
    const uniqueName = Date.now() + "-" + Math.round(Math.random() * 1e9) + path.extname(sanitizedName);
    cb(null, uniqueName);
  },
});

// File filter for security
const fileFilter = (req, file, cb) => {
  console.log("File upload attempt:", {
    originalname: file.originalname,
    mimetype: file.mimetype,
    size: file.size,
  });

  // Allowed file extensions
  const allowedExtensions = /\.(jpeg|jpg|png|gif|pdf|doc|docx)$/i;

  // Allowed MIME types
  const allowedMimeTypes = [
    "image/jpeg", // Fixed: removed the 'å' character
    "image/jpg",
    "image/png",
    "image/gif",
    "application/pdf",
    "application/msword",
    "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    "application/octet-stream", // Added: some browsers send this for images
  ];

  const extname = allowedExtensions.test(file.originalname.toLowerCase());
  const mimetype = allowedMimeTypes.includes(file.mimetype.toLowerCase());

  // Additional check: if it's octet-stream but has image extension, allow it
  const isImageExtension = /\.(jpeg|jpg|png|gif)$/i.test(file.originalname.toLowerCase());
  const isOctetStream = file.mimetype === "application/octet-stream";

  if ((extname && mimetype) || (isOctetStream && isImageExtension)) {
    console.log("File accepted:", file.originalname);
    return cb(null, true);
  } else {
    console.log("File rejected:", {
      filename: file.originalname,
      mimetype: file.mimetype,
      extname_valid: extname,
      mimetype_valid: mimetype,
    });
    const errorMsg = `File type not allowed. Only these types are accepted: jpeg, jpg, png, gif, pdf, doc, docx. Uploaded file type: ${file.mimetype}`;
    cb(new Error(errorMsg));
  }
};

// Configure multer
const upload = multer({
  storage: storage,
  limits: {
    fileSize: parseInt(process.env.MAX_FILE_SIZE) || 5 * 1024 * 1024, // 5MB default
  },
  fileFilter: fileFilter,
});

module.exports = upload;
