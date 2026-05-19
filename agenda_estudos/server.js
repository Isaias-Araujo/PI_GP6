require('dotenv').config();

const express = require('express');
const session = require('express-session');
const path = require('path');

const app = express();

require('./src/config/database')();

app.set('view engine', 'ejs');
app.set('views', path.join(__dirname, 'src/views'));

app.use(express.urlencoded({ extended: true }));
app.use(express.static(path.join(__dirname, 'src/public')));

app.use(session({
  secret: process.env.SESSION_SECRET,
  resave: false,
  saveUninitialized: false
}));

app.use(require('./src/routes/authRoutes'));
app.use(require('./src/routes/dashboardRoutes'));

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log('Servidor rodando na porta ' + PORT);
});