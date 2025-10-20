-- SQL para crear la tabla deudas_trabajador
DROP TABLE IF EXISTS `deudas_trabajador`;
CREATE TABLE `deudas_trabajador`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `trabajador_id` int(11) NULL DEFAULT NULL,
  `monto` decimal(10, 2) NULL DEFAULT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `fecha_registro` date NULL DEFAULT NULL,
  `saldada` tinyint(1) NULL DEFAULT NULL,
  `fecha_saldo` date NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `trabajador_id`(`trabajador_id`) USING BTREE,
  CONSTRAINT `deudas_trabajador_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;
