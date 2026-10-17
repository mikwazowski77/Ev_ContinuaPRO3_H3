const express = require('express');
const cors = require('cors');
const productoRoutes = require('./routes/productoRoutes');

const app = express();
app.use(express.json());
app.use(cors());

// Rutas base con prefijo limpio
app.use('/api/productos', productoRoutes);

const PORT = 4500; // Puerto completamente diferente
app.listen(PORT, () => {
    console.log(`Servidor MVC de Miguel corriendo en el puerto ${PORT}`);
});