const express = require('express');
const path = require('path');
const session = require('express-session');
const mysql = require('mysql2');
const app = express();
const port = 3000;

// Middleware to parse POST request data
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Session setup
app.use(session({
  secret: 'your_secret_key',  // Replace with a real secret key
  resave: false,
  saveUninitialized: true
}));

// MySQL Database Connection
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'quickiejeepney'
});

// Connect to MySQL
db.connect((err) => {
  if (err) {
    console.error('MySQL connection error:', err);
    throw err;
  }
  console.log('Connected to MySQL Database');
});

// Serve static files from 'admin' folder
app.use(express.static(path.join(__dirname, 'admin')));

// Route to serve the login page
app.get('/', (req, res) => {
  if (req.session.admin) {
    return res.redirect('/admin');  // Redirect logged-in users to the dashboard
  }

  // Set headers to prevent caching
  res.setHeader('Cache-Control', 'no-store');
  res.sendFile(path.join(__dirname, 'admin', 'login.html'));
});

// Login route
app.post('/login', (req, res) => {
  const { email, password } = req.body;

  db.query('SELECT * FROM admin WHERE email = ?', [email], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Database query error' });
    }

    if (result.length > 0) {
      const admin = result[0];

      if (password === admin.password) {
        req.session.admin = admin;  // Store admin data in session
        return res.json({ success: true });
      } else {
        return res.status(401).json({ success: false, message: 'Invalid credentials' });
      }
    } else {
      return res.status(401).json({ success: false, message: 'Invalid credentials' });
    }
  });
});

// Admin route - Only accessible if logged in as admin
app.get('/admin', (req, res) => {
  if (req.session.admin) {
    res.sendFile(path.join(__dirname, 'admin', 'admin-dashboard.html'));
  } else {
    res.status(403).json({ success: false, message: 'Forbidden - You must be logged in' });
  }
});

// Route to fetch users from the database
app.get('/api/users', (req, res) => {
  db.query('SELECT userID, firstName, lastName, contactNumber, email, occupation, isBanned FROM user', (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Database query error' });
    }
    res.json(result);  // Send the user data as JSON
  });
});

// Route to add a new manager to the database
app.post('/api/add-manager', (req, res) => {
  const { firstName, lastName, contactNumber, email, password, occupation } = req.body;

  // Validate input
  if (!firstName || !lastName || !contactNumber || !email || !password || !occupation) {
    return res.status(400).json({ success: false, message: 'All fields are required' });
  }

  // Insert the new manager into the database
  const query = 'INSERT INTO user (firstName, lastName, contactNumber, email, password, occupation) VALUES (?, ?, ?, ?, ?, ?)';
  db.query(query, [firstName, lastName, contactNumber, email, password, occupation], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Database insertion error' });
    }

    console.log('Manager added successfully:', result.insertId);
    res.status(201).json({ success: true, message: 'Manager added successfully', userID: result.insertId });
  });
});

// Route to add a new admin to the database
app.post('/api/add-admin', (req, res) => {
  const { firstName, lastName, email, password } = req.body;

  // Validate input
  if (!firstName || !lastName || !email || !password) {
    return res.status(400).json({ success: false, message: 'All fields are required' });
  }

  // Insert the new admin into the database
  const query = 'INSERT INTO admin (firstName, lastName, email, password) VALUES (?, ?, ?, ?)';
  db.query(query, [firstName, lastName, email, password], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Database insertion error' });
    }

    console.log('Admin added successfully:', result.insertId);
    res.status(201).json({ success: true, message: 'Admin added successfully', adminID: result.insertId });
  });
});

// Route to fetch jeepneys from the database
app.get('/api/jeepneys', (req, res) => {
  const query = 'SELECT jeepneyID, driverID, plateNumber, capacity, occupied, route, type, departure_time, jeep_image, status FROM jeepney';
  db.query(query, (err, results) => {
      if (err) {
          console.error('Error fetching jeepneys:', err);
          return res.status(500).json({ error: 'Failed to fetch jeepneys' });
      }
      results = results.map(jeepney => {
        if (jeepney.jeep_image) {
            jeepney.jeep_image = jeepney.jeep_image.toString('base64'); // Convert BLOB to Base64
        }
        return jeepney;
    });

      res.json(results);
  });
});

// Delete a jeepney based on the jeepneyID
app.delete('/api/jeepney/:jeepneyID', (req, res) => {
  const jeepneyID = req.params.jeepneyID;  

  const query = 'DELETE FROM jeepney WHERE jeepneyID = ?';

  // Execute the query
  db.query(query, [jeepneyID], (err, results) => {
      if (err) {
          console.error('Error deleting jeepney:', err);
          return res.status(500).json({ success: false, message: 'Failed to delete jeepney' });
      }

      if (results.affectedRows > 0) {
          // If at least one row was deleted
          res.json({ success: true, message: 'Jeepney deleted successfully' });
      } else {
          // If no jeepney with the given ID was found
          res.status(404).json({ success: false, message: 'Jeepney not found' });
      }
  });
});

app.post('/api/add-jeepney', (req, res) => {
  const { driverID, plateNumber, capacity, occupied, route, type, status } = req.body;

  const newDriverID = "System_Assigned_ID"; 
  const newRoute = "System_Assigned_Route";  
  const newStatus = "active";                

  db.query(
    'INSERT INTO jeepney (jeepneyID, driverID, plateNumber, capacity, occupied, route, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
    [newDriverID, plateNumber, capacity, 0, newRoute, type, newStatus], // Set occupied to 0 initially
    (err, results) => {
      if (err) {
        return res.status(500).json({ success: false, message: 'Database error' });
      }
      res.json({ success: true });
    }
  );
});

// Route to ban a user
app.post('/api/ban-user/:userID', (req, res) => {
  const userID = req.params.userID;

  // Update the user's status to 'banned' (1 for banned, 0 for not banned)
  const query = 'UPDATE user SET isBanned = 1 WHERE userID = ?';
  db.query(query, [userID], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Error banning user' });
    }

    if (result.affectedRows === 0) {
      // If no rows are affected, that means no user with the given ID was found
      return res.status(404).json({ success: false, message: 'User not found' });
    }

    res.json({ success: true, message: 'User banned successfully' });
  });
});

// Route to unban a user
app.post('/api/unban-user/:userID', (req, res) => {
  const userID = req.params.userID;

  // Update the user's status to 'unbanned' (0 for unbanned)
  const query = 'UPDATE user SET isBanned = 0 WHERE userID = ?';
  db.query(query, [userID], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Error unbanning user' });
    }

    if (result.affectedRows === 0) {
      return res.status(404).json({ success: false, message: 'User not found' });
    }

    res.json({ success: true, message: 'User unbanned successfully' });
  });
});

// Route to delete a user
app.delete('/api/delete-user/:userID', (req, res) => {
  const userID = req.params.userID;

  // Delete the user from the database
  const query = 'DELETE FROM user WHERE userID = ?';
  db.query(query, [userID], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Error deleting user' });
    }

    res.json({ success: true, message: 'User deleted successfully' });
  });
});

// Check if the user is logged in
app.get('/api/check-login', (req, res) => {
  if (req.session.admin) {
    res.json({ loggedIn: true });
  } else {
    res.json({ loggedIn: false });
  }
});

// Logout route (POST request)
app.post('/logout', (req, res) => {
  req.session.destroy((err) => {
    if (err) {
      console.error('Error destroying session:', err);
      return res.status(500).json({ success: false, message: 'Failed to log out' });
    }
    console.log('Session destroyed successfully');
    res.status(200).json({ success: true });
  });
});

// Start the server
app.listen(port, '0.0.0.0',() => {
  console.log(`Server is running at http://localhost:${port}`);
});
