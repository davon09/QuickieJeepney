const express = require('express');
const path = require('path');
const app = express();
const port = 3000;

// Serve static files from the 'public' directory
app.use(express.static(path.join(__dirname, 'public')));

// Serve the admin homepage
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'admin', 'public/admin.html'));
});

// Start the server
app.listen(port, () => {
    console.log(`Admin dashboard running at http://localhost:${port}`);
});
