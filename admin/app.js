const express = require('express');
const mysql = require('mysql');
const bodyParser = require('body-parser');
const app = express();

// Middleware
app.use(bodyParser.urlencoded({ extended: true }));
app.use(express.static('public'));
app.set('view engine', 'ejs');

// Database Connection
const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'quickiejeepney' // Replace with your database name
});

db.connect(err => {
  if (err) throw err;
  console.log('Connected to WAMP MySQL Database.');
});

// Routes
app.get('/', (req, res) => {
  res.redirect('/users');
});

// View Registered Users
app.get('/users', (req, res) => {
  const query = 'SELECT * FROM users'; // Replace with your table name
  db.query(query, (err, results) => {
    if (err) throw err;
    res.render('index', { section: 'Users', data: results });
  });
});

// Jeepney and Driver Registration
app.get('/register', (req, res) => {
  res.render('index', { section: 'Register', data: null });
});

app.post('/register', (req, res) => {
  const { driverName, jeepneyPlate } = req.body;
  const query = 'INSERT INTO jeepneys (driver_name, plate_number) VALUES (?, ?)';
  db.query(query, [driverName, jeepneyPlate], (err, results) => {
    if (err) throw err;
    res.redirect('/users');
  });
});

// Server
app.listen(3000, () => {
  console.log('Server running on http://localhost:3000');
});
