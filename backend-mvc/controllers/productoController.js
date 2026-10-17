const pool = require('../config/db');

const productoController = {
    // Listar solo activos
    async obtenerActivos(req, res) {
        try {
            const [rows] = await pool.query(
                "SELECT id, nombre, proveedor, fecha_vencimiento, precio, stock, eliminado FROM Producto WHERE eliminado = 0"
            );
            res.json(rows);
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    },

    // Crear producto
    async crear(req, res) {
        try {
            const { nombre, proveedor, fecha_vencimiento, precio, stock } = req.body;
            const query = "INSERT INTO Producto (nombre, proveedor, fecha_vencimiento, precio, stock, eliminado) VALUES (?, ?, ?, ?, ?, 0)";
            const [result] = await pool.query(query, [nombre, proveedor, fecha_vencimiento, precio, stock]);
            res.status(201).json({ mensaje: "Registrado con éxito", id: result.insertId });
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    },

    // Actualizar producto
    async actualizar(req, res) {
        try {
            const { id } = req.params;
            const { nombre, proveedor, fecha_vencimiento, precio, stock } = req.body;
            const query = "UPDATE Producto SET nombre = ?, proveedor = ?, fecha_vencimiento = ?, precio = ?, stock = ? WHERE id = ?";
            await pool.query(query, [nombre, proveedor, fecha_vencimiento, precio, stock, id]);
            res.json({ mensaje: "Actualizado correctamente" });
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    },

    // Baja lógica (eliminado = 1)
    async bajaLogica(req, res) {
        try {
            const { id } = req.params;
            const query = "UPDATE Producto SET eliminado = 1 WHERE id = ?";
            await pool.query(query, [id]);
            res.json({ mensaje: "Baja lógica aplicada correctamente" });
        } catch (error) {
            res.status(500).json({ error: error.message });
        }
    }
};

module.exports = productoController;