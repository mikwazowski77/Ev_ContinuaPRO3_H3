const express = require('express');
const router = express.Router();
const controller = require('../controllers/productoController');

router.get('/', controller.obtenerActivos);
router.post('/', controller.crear);
router.put('/:id', controller.actualizar);
router.delete('/:id', controller.bajaLogica);

module.exports = router;