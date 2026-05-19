const express = require('express');
const router = express.Router();
const mysql = require('mysql2/promise');

async function db(){
  return mysql.createConnection({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME
  });
}

function formatarTempo(segundos){

  const h = Math.floor(segundos / 3600);
  const m = Math.floor((segundos % 3600) / 60);
  const s = segundos % 60;

  return String(h).padStart(2,'0') + ':' +
         String(m).padStart(2,'0') + ':' +
         String(s).padStart(2,'0');
}

router.get('/dashboard', async (req,res)=>{

  if(!req.session.user){
    return res.redirect('/');
  }

  const conn = await db();

  const [tarefas] = await conn.query(
    'SELECT * FROM tarefas WHERE usuario_id=? ORDER BY id DESC',
    [req.session.user.id]
  );

  tarefas.forEach(t => {

    let total = t.tempo_gasto || 0;

    if(t.status === 'running' && t.data_inicio){
      total += Math.floor(
        (Date.now() - new Date(t.data_inicio)) / 1000
      );
    }

    t.tempo_formatado = formatarTempo(total);
  });

  res.render('dashboard',{
    user:req.session.user,
    tarefas
  });
});

router.post('/nova-tarefa', async (req,res)=>{

  const conn = await db();

  await conn.query(
    `INSERT INTO tarefas
    (usuario_id,titulo,descricao,data_entrega)
    VALUES (?,?,?,?)`,
    [
      req.session.user.id,
      req.body.titulo,
      req.body.descricao,
      req.body.data_entrega || null
    ]
  );

  res.redirect('/dashboard');
});

router.post('/iniciar/:id', async (req,res)=>{

  const conn = await db();

  await conn.query(
    `UPDATE tarefas
     SET status='running',
         data_inicio=NOW()
     WHERE id=?`,
    [req.params.id]
  );

  res.redirect('/dashboard');
});

router.post('/pausar/:id', async (req,res)=>{

  const conn = await db();

  const [rows] = await conn.query(
    'SELECT * FROM tarefas WHERE id=?',
    [req.params.id]
  );

  const tarefa = rows[0];

  let segundos = 0;

  if(tarefa.data_inicio){
    segundos = Math.floor(
      (Date.now() - new Date(tarefa.data_inicio)) / 1000
    );
  }

  await conn.query(
    `UPDATE tarefas
     SET status='paused',
         tempo_gasto=tempo_gasto + ?
     WHERE id=?`,
    [segundos, req.params.id]
  );

  res.redirect('/dashboard');
});

router.post('/retomar/:id', async (req,res)=>{

  const conn = await db();

  await conn.query(
    `UPDATE tarefas
     SET status='running',
         data_inicio=NOW()
     WHERE id=?`,
    [req.params.id]
  );

  res.redirect('/dashboard');
});

router.post('/concluir/:id', async (req,res)=>{

  const conn = await db();

  const [rows] = await conn.query(
    'SELECT * FROM tarefas WHERE id=?',
    [req.params.id]
  );

  const tarefa = rows[0];

  let total = tarefa.tempo_gasto || 0;

  if(tarefa.status === 'running' && tarefa.data_inicio){

    total += Math.floor(
      (Date.now() - new Date(tarefa.data_inicio)) / 1000
    );
  }

  await conn.query(
    `UPDATE tarefas
     SET concluido=1,
         status='completed',
         tempo_gasto=?,
         data_fim=NOW()
     WHERE id=?`,
    [total, req.params.id]
  );

  res.redirect('/dashboard');
});

router.post('/excluir/:id', async (req,res)=>{

  const conn = await db();

  await conn.query(
    'DELETE FROM tarefas WHERE id=?',
    [req.params.id]
  );

  res.redirect('/dashboard');
});

module.exports = router;