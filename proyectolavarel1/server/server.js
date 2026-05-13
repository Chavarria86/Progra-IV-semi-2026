const http = require('http').server(),
        io = require('socket.io')(http);


io.on('connection', (socket) => {
    console.log('Un usuario se ha conectado');
});   
    
http.listen(3000, () => {
    console.log('El servidor esta corriendo en el puerto 3000');
});
  

