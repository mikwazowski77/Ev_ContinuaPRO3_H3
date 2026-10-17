

USE db_minimarket_pro413;

CREATE TABLE IF NOT EXISTS Producto (
    id                INT            NOT NULL AUTO_INCREMENT,
    nombre            VARCHAR(100)   NOT NULL,
    proveedor         VARCHAR(100)   NOT NULL,
    fecha_vencimiento DATE           NOT NULL,
    precio            DECIMAL(10,2)  NOT NULL,
    stock             INT            NOT NULL,
    eliminado         TINYINT(1)     NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    CONSTRAINT chk_producto_precio CHECK (precio >= 0),
    CONSTRAINT chk_producto_stock  CHECK (stock >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;