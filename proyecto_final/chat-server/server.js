const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const mongoose = require('mongoose');
const cors = require('cors');

const app = express();
app.use(cors());

const server = http.createServer(app);
const io = new Server(server, {
  cors: {
    origin: '*',
    methods: ['GET', 'POST']
  }
});

// Conexión a MongoDB (base de datos: chast_genesis, colección: mensajes)
const MONGO_URI = 'mongodb://127.0.0.1:27017/chast_genesis';
mongoose.connect(MONGO_URI)
  .then(() => console.log('Conectado a MongoDB con éxito en:', MONGO_URI))
  .catch(err => console.error('Error al conectar a MongoDB:', err));

// Esquema de Mensajes
const messageSchema = new mongoose.Schema({
  sala: { type: String, required: true, index: true },
  remitente_id: { type: Number, required: true },
  destinatario_id: { type: Number, required: true },
  texto: { type: String, required: true },
  fecha: { type: Date, default: Date.now }
});

const Mensaje = mongoose.model('Mensaje', messageSchema, 'mensajes');

// Lógica de Sockets
io.on('connection', (socket) => {
  console.log('Cliente conectado:', socket.id);

  // Registrar el ID del usuario en una sala individual para notificaciones globales
  socket.on('register_user', (usuario_id) => {
    const userRoom = `user_${usuario_id}`;
    socket.join(userRoom);
    console.log(`Usuario ${usuario_id} registrado en su canal personal: ${userRoom}`);
  });

  // Unirse a una sala de chat
  socket.on('join_room', async ({ remitente_id, destinatario_id }) => {
    // Generar ID de sala simétrica ordenando los IDs
    const ids = [Number(remitente_id), Number(destinatario_id)].sort((a, b) => a - b);
    const roomId = `sala_${ids[0]}_${ids[1]}`;

    socket.join(roomId);
    console.log(`Usuario ${remitente_id} se unió a la sala: ${roomId}`);

    try {
      // Recuperar historial de mensajes (últimos 100)
      const historial = await Mensaje.find({ sala: roomId })
        .sort({ fecha: 1 })
        .limit(100);
      
      // Enviar historial solo al usuario que se acaba de conectar
      socket.emit('chat_history', historial);
    } catch (err) {
      console.error('Error al recuperar historial de chat:', err);
    }
  });

  // Recibir y transmitir mensaje
  socket.on('send_message', async ({ remitente_id, remitente_nombre, destinatario_id, texto }) => {
    const ids = [Number(remitente_id), Number(destinatario_id)].sort((a, b) => a - b);
    const roomId = `sala_${ids[0]}_${ids[1]}`;

    try {
      // Guardar en MongoDB
      const nuevoMensaje = new Mensaje({
        sala: roomId,
        remitente_id: Number(remitente_id),
        destinatario_id: Number(destinatario_id),
        texto: texto.trim()
      });
      const mensajeGuardado = await nuevoMensaje.save();

      // Convertir a objeto JS plano para añadir metadatos dinámicos
      const payload = {
        ...mensajeGuardado.toObject(),
        remitente_nombre: remitente_nombre || 'Usuario'
      };

      // Emitir en tiempo real a todos en la sala (ambos participantes)
      io.to(roomId).emit('new_message', payload);

      // Emitir al canal individual del destinatario para notificaciones push
      io.to(`user_${destinatario_id}`).emit('msg_notification', payload);

      console.log(`Mensaje enviado en sala ${roomId} por ${remitente_id} (${remitente_nombre || 'Usuario'}): ${texto}`);
    } catch (err) {
      console.error('Error al guardar/emitir mensaje:', err);
    }
  });

  socket.on('disconnect', () => {
    console.log('Cliente desconectado:', socket.id);
  });
});

const PORT = 3000;
server.listen(PORT, '0.0.0.0', () => {
  console.log(`Servidor de chat ejecutándose en http://localhost:${PORT}`);
});
