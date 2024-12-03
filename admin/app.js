const mysql = require('mysql2');
const express = require('express');
const bodyParser = require('body-parser');
const app = express();

app.set('view engine', 'ejs'); // Set EJS as the template engine
app.set('views', './views');

app.use(bodyParser.urlencoded({ extended: true }));
app.use(express.static('public'));

// Database connection
const connection = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: 'password',
  database: 'name'
});

// Check connection
connection.connect((err) => {
  if (err) throw err;
  console.log('Connected to MySQL');
});

app.get('/', (req, res) => {
  res.redirect('/admin');
});

// Route for dashboard
app.get('/dashboard', (req, res) => {
  const totalUsers = users.length;
  const totalJeepneys = jeepneys.length;
  const jeepneysDeparted = calculateJeepneyStats.jeepneysDeparted.length;
  const jeepneysAboutToDepart = calculateJeepneyStats.jeepneysAboutToDepart.length;
  const jeepneysLeft = calculateJeepneyStats.jeepneysLeft.length;

  res.render('admin-panel', {
    section: 'Dashboard',
    stats: {
      totalUsers,
      totalJeepneys,
      jeepneysDeparted,
      jeepneysAboutToDepart,
      jeepneysLeft,
    },
  });
});

// Route for adding a manager
app.post('/add-manager', async (req, res) => {
  const { firstName, lastName, email } = req.body;
  const managerId = generateRandomManagerID();
  const password = generateRandomPassword();

  try {
    // Check if the email already exists
    const [rows] = await connection.promise().query(
      'SELECT * FROM manager WHERE email = ?',
      [email]
    );

    if (rows.length > 0) {
      return res.send('Error: User with this email already exists.');
    }

    // Insert new manager
    await connection.promise().query(
      'INSERT INTO manager (managerId, lastName, firstName, password, email) VALUES (?, ?, ?, ?, ?)',
      [managerId, lastName, firstName, password, email]
    );

    res.send('New manager added successfully!');
  } catch (err) {
    console.error(err);
    res.status(500).send('Internal Server Error');
  }
});

// Route for adding a vehicle
app.post('/add-vehicle', async (req, res) => {
  const {
    plateNumber,
    capacity,
    occupied,
    route,
    type,
    jeepImage,
    departureTime
  } = req.body;

  const jeepneyId = generateRandomJeepneyID();
  const driverId = generateRandomDriverID();

  try {
    // Check if the plate number already exists
    const [rows] = await connection.promise().query(
      'SELECT * FROM jeepney WHERE plateNumber = ?',
      [plateNumber]
    );

    if (rows.length > 0) {
      return res.send('Error: Jeepney with this Plate Number already exists.');
    }

    // Insert new vehicle
    await connection.promise().query(
      'INSERT INTO jeepney (jeepneyId, driverId, plateNumber, capacity, occupied, route, type, jeepImage, departureTime) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
      [jeepneyId, driverId, plateNumber, capacity, occupied, route, type, jeepImage, departureTime]
    );

    res.send('New vehicle added successfully!');
  } catch (err) {
    console.error(err);
    res.status(500).send('Internal Server Error');
  }
});

// Route for deleting a user
app.post('/delete-user', async (req, res) => {
  const { email } = req.body;

  try {
    // Check if the user exists
    const [rows] = await connection.promise().query(
      'SELECT * FROM users WHERE email = ?',
      [email]
    );

    if (rows.length === 0) {
      return res.send('Error: User with this email does not exist.');
    }

    // Delete the user
    await connection.promise().query(
      'DELETE FROM users WHERE email = ?',
      [email]
    );

    res.send('User deleted successfully!');
  } catch (err) {
    console.error(err);
    res.status(500).send('Internal Server Error');
  }
});

// change function according to db 
function generateRandomManagerID() {
  return `MGR-${Math.floor(Math.random() * 100000)}`;
}

function generateRandomJeepneyID() {
  return `JPN-${Math.floor(Math.random() * 100000)}`;
}

function generateRandomDriverID() {
  return `DRV-${Math.floor(Math.random() * 100000)}`;
}

function generateRandomPassword() {
  const characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  let password = '';
  for (let i = 0; i < 4; i++) {
      const randomIndex = Math.floor(Math.random() * characters.length);
      password += characters[randomIndex];
  }
  return password;
}

// Utility to calculate jeepney departure status
const calculateJeepneyStats = () => {
  const now = new Date();
  const departed = [];
  const aboutToDepart = [];
  const left = [];

  jeepneys.forEach((jeepney) => {
    const departureTime = new Date(jeepney.departureTime);
    const timeDifference = (departureTime - now) / (1000 * 60); // Difference in minutes

    if (timeDifference < 0) {
      departed.push(jeepney);
    } else if (timeDifference <= 30) {
      aboutToDepart.push(jeepney);
    } else {
      left.push(jeepney);
    }
  });

  return {
    jeepneysDeparted: departed.length,
    jeepneysAboutToDepart: aboutToDepart.length,
    jeepneysLeft: left.length,
  };
};

// Start server
const PORT = 3000;
app.listen(PORT, () => {
  console.log(`Server is running on http://localhost:${PORT}`);
});
