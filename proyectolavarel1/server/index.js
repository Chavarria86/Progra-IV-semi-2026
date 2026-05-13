const http = require('http');

    server = http.createServer((req, res) => {
    res.statusCode = 200;
    res.writehead(200, { "Content-Type": "text/plain" });
    res.end('HOLA MUNDO...');
});

server.listen(3000, () => {
    console.log('El servidor esta corriendo en el puerto 3000');
});