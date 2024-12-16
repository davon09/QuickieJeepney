const express = require('express');
const path = require('path');
const session = require('express-session');
const mysql = require('mysql2');
const multer = require('multer');
const app = express();
const port = 3000;
const bcrypt = require('bcrypt');
const saltRounds = 10;

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

// Multer setup for file upload
const storage = multer.memoryStorage(); // Store file in memory
const upload = multer({ storage: storage });

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

  // Query the database for the user based on email
  db.query('SELECT * FROM admin WHERE email = ?', [email], (err, result) => {
    if (err) {
        console.error('Database query error:', err);
        return res.status(500).json({ success: false, message: 'Database query error' });
    }

    if (result.length === 0) {
        return res.status(401).json({ success: false, message: 'Invalid credentials' });
    }

    const admin = result[0];

    // Check if the password is hashed (using a simple rule, assuming bcrypt hash)
    if (admin.password && admin.password.includes('$')) {
        // Password is hashed (likely using bcrypt)
        bcrypt.compare(password, admin.password, (err, isMatch) => {
            if (err) {
                console.error('Error comparing password:', err);
                return res.status(500).json({ success: false, message: 'Error comparing password' });
            }
            if (isMatch) {
                req.session.admin = admin;
                res.json({ success: true });
            } else {
                res.status(401).json({ success: false, message: 'Invalid credentials' });
            }
        });
    } else {
        // Password is stored as plain text (fallback case)
        if (password === admin.password) {
            req.session.admin = admin;
            res.json({ success: true });
        } else {
            res.status(401).json({ success: false, message: 'Invalid credentials' });
        }
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

// Add Manager Route
app.post('/api/add-manager', (req, res) => {
  const { firstName, lastName, contactNumber, email, password, occupation } = req.body;

  // Hash the password before saving it
  bcrypt.hash(password, saltRounds, (err, hashedPassword) => {
    if (err) {
      console.error('Error hashing password:', err);
      return res.status(500).json({ success: false, message: 'Error hashing password' });
    }

    // Insert the new manager into the database with the hashed password
    const query = 'INSERT INTO user (firstName, lastName, contactNumber, email, password, occupation) VALUES (?, ?, ?, ?, ?, ?)';
    db.query(query, [firstName, lastName, contactNumber, email, hashedPassword, occupation], (err, result) => {
      if (err) {
        console.error('MySQL query error:', err);
        return res.status(500).json({ success: false, message: 'Database insertion error' });
      }

      console.log('Manager added successfully:', result.insertId);
      res.status(201).json({ success: true, message: 'Manager added successfully', userID: result.insertId });
    });
  });
});

// Add Admin Route
app.post('/api/add-admin', (req, res) => {
  const { firstName, lastName, email, password } = req.body;

  // Hash the password before saving it
  bcrypt.hash(password, saltRounds, (err, hashedPassword) => {
    if (err) {
      console.error('Error hashing password:', err);
      return res.status(500).json({ success: false, message: 'Error hashing password' });
    }

    // Insert the new admin into the database with the hashed password
    const query = 'INSERT INTO admin (firstName, lastName, email, password) VALUES (?, ?, ?, ?)';
    db.query(query, [firstName, lastName, email, hashedPassword], (err, result) => {
      if (err) {
        console.error('MySQL query error:', err);
        return res.status(500).json({ success: false, message: 'Database insertion error' });
      }

      console.log('Admin added successfully:', result.insertId);
      res.status(201).json({ success: true, message: 'Admin added successfully', adminID: result.insertId });
    });
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

// Add Jeepney route (with image upload)
app.post('/api/add-jeepney', (req, res) => {
  const { plateNumber, capacity, type, jeepneyImage } = req.body;

  // Ensure that jeepneyImage is provided
  if (!jeepneyImage) {
    return res.status(400).json({ success: false, message: 'Image is required' });
  }

  // Now insert into the database, including the jeepneyImage (base64)
  const query = 'INSERT INTO jeepney (plateNumber, capacity, type, jeep_image) VALUES (?, ?, ?, ?)';
  db.query(query, [plateNumber, capacity, type, jeepneyImage], (err, result) => {
    if (err) {
      console.error('MySQL query error:', err);
      return res.status(500).json({ success: false, message: 'Database insertion error' });
    }

    console.log('Jeepney added successfully:', result.insertId);
    res.status(201).json({ success: true, message: 'Jeepney added successfully', jeepneyID: result.insertId });
  });
});


// Route to assign a driver to a jeepney
app.post('/api/assignDriver', (req, res) => {
  const { jeepneyID, driverID } = req.body;

  // Ensure jeepneyID and driverID are provided
  if (!jeepneyID || !driverID) {
      return res.status(400).json({ error: 'Jeepney and Driver IDs are required.' });
  }

  // SQL query to update the jeepney with the assigned driver
  const query = 'UPDATE jeepney SET driverID = ? WHERE jeepneyID = ?';

  db.query(query, [driverID, jeepneyID], (err, result) => {
      if (err) {
          console.error('Error assigning driver:', err);
          return res.status(500).json({ error: 'Database error' });
      }

      // Respond with success
      res.json({ success: true });
  });
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

// Route to fetch drivers from the database
app.get('/api/drivers', (req, res) => {
  const query = 'SELECT driverID, firstName, lastName, contactNumber FROM driver';
  db.query(query, (err, results) => {
      if (err) {
          console.error('Error fetching drivers:', err);
          return res.status(500).json({ success: false, message: 'Failed to fetch drivers' });
      }
      res.json(results); // Send the driver data as JSON
  });
});

// Add Driver Route
app.post('/api/add-drivers', (req, res) => {
  const { firstName, lastName, contactNumber } = req.body;

    // Insert the new driver into the database
    const query = 'INSERT INTO driver (firstName, lastName, contactNumber) VALUES (?, ?, ?)';
    db.query(query, [firstName, lastName, contactNumber], (err, result) => {
      if (err) {
        console.error('MySQL query error:', err);
        return res.status(500).json({ success: false, message: 'Database insertion error' });
      }

      console.log('Driver added successfully:', result.insertId);
      res.status(201).json({ success: true, message: 'Driver added successfully', driverID: result.insertId });
    });
});

// Route to delete a driver
app.delete('/api/delete-driver/:driverID', (req, res) => {
  const driverID = req.params.driverID;
  console.log('Driver ID received for deletion:', driverID); // Debug log

  const query = 'DELETE FROM driver WHERE driverID = ?';
  db.query(query, [driverID], (err, result) => {
      if (err) {
          console.error('MySQL query error:', err);
          return res.status(500).json({ success: false, message: 'Error deleting driver' });
      }

      if (result.affectedRows > 0) {
          res.json({ success: true, message: 'Driver deleted successfully' });
      } else {
          console.log('No driver found with ID:', driverID); // Debug log
          res.status(404).json({ success: false, message: 'Driver not found' });
      }
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
