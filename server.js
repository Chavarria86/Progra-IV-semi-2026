/**
 * server.js
 * Servidor de desarrollo local con Node.js
 * Configura automáticamente los headers COOP/COEP necesarios para OPFS + SharedArrayBuffer
 *
 * USO:
 *   node server.js
 *   Luego abrir: http://localhost:8080
 *
 * REQUISITOS:
 *   Node.js >= 14  (sin dependencias npm adicionales)
 */

const http = require('http');
const fs   = require('fs');
const path = require('path');

const PORT = 8080;
const BASE = __dirname;

const MIME = {
    '.html': 'text/html; charset=utf-8',
    '.js':   'application/javascript; charset=utf-8',
    '.css':  'text/css; charset=utf-8',
    '.wasm': 'application/wasm',
    '.json': 'application/json; charset=utf-8',
    '.png':  'image/png',
    '.ico':  'image/x-icon',
    '.svg':  'image/svg+xml',
    '.map':  'application/json',
};

const server = http.createServer((req, res) => {
    // Eliminar query string
    let urlPath = req.url.split('?')[0];
    if (urlPath === '/') urlPath = '/index.html';

    const filePath = path.join(BASE, urlPath);
    const ext      = path.extname(filePath).toLowerCase();
    const mimeType = MIME[ext] || 'application/octet-stream';

    // Headers OBLIGATORIOS para OPFS + SharedArrayBuffer
    res.setHeader('Cross-Origin-Opener-Policy',   'same-origin');
    res.setHeader('Cross-Origin-Embedder-Policy',  'require-corp');
    res.setHeader('Cross-Origin-Resource-Policy',  'same-origin');

    fs.readFile(filePath, (err, data) => {
        if (err) {
            res.writeHead(404, { 'Content-Type': 'text/plain' });
            res.end(`Archivo no encontrado: ${urlPath}`);
            return;
        }
        res.writeHead(200, { 'Content-Type': mimeType });
        res.end(data);
    });
});

server.listen(PORT, () => {
    console.log(`\n✅ Servidor iniciado en: http://localhost:${PORT}`);
    console.log(`   Headers COOP/COEP configurados para OPFS + SharedArrayBuffer`);
    console.log(`   Presiona Ctrl+C para detener.\n`);
});
