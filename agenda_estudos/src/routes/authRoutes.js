const express = require('express');
const router = express.Router();
const mysql = require('mysql2/promise');
const bcrypt = require('bcrypt');

async function db(){
  return mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
  });
}

router.get('/', (req,res)=>{
  res.render('index');
});

router.post('/login', async (req,res)=>{

  const conn = await db();

  const [users] = await conn.query(
    'SELECT * FROM usuarios WHERE email=?',
    [req.body.email]
  );

  if(users.length === 0){
    return res.send('Usuário não encontrado');
  }

  const user = users[0];

  const ok = await bcrypt.compare(
    req.body.password,
    user.senha_hash
  );

  if(!ok){
    return res.send('Senha inválida');
  }

  req.session.user = user;

  res.redirect('/dashboard');
});

router.get('/logout',(req,res)=>{
  req.session.destroy(()=>{
    res.redirect('/');
  });
});

module.exports = router;