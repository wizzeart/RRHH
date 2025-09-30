-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 30-09-2025 a las 15:45:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `mb_recursos_humanos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `accesos_informaticos`
--

CREATE TABLE `accesos_informaticos` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `servicio` varchar(255) DEFAULT NULL,
  `fecha_concesion` date DEFAULT NULL,
  `fecha_revocacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ayudas`
--

CREATE TABLE `ayudas` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `tipo_ayuda` varchar(255) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `fecha_entrega` date DEFAULT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `numero_tarjeta_salario` varchar(100) DEFAULT NULL,
  `numero_cuenta_estandar` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bolsa_empleo`
--

CREATE TABLE `bolsa_empleo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `apellidos` varchar(255) DEFAULT NULL,
  `ci_bolsa_empleo` int(12) DEFAULT NULL,
  `curriculum` varchar(255) DEFAULT NULL,
  `cargo_postulado_id` int(11) DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `estatus` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bolsa_empleo`
--

INSERT INTO `bolsa_empleo` (`id`, `nombre`, `apellidos`, `ci_bolsa_empleo`, `curriculum`, `cargo_postulado_id`, `telefono`, `email`, `fecha_registro`, `estatus`) VALUES
(1, 'Pedro Antonio', 'Manduley Roca', NULL, 'uploads/curriculos/pruebaCV.docx', 1, '7777777', 'test@test.cu', '2025-09-19 19:08:53', 'activo'),
(2, 'Pablo', 'Sanchez', NULL, 'uploads/curriculos/cv_68cdac9e3441f_pruebaCV (1).docx', 1, '+53 5 1234567', 'pablo@allnovu.net', '2025-09-19 04:00:00', 'pendiente'),
(3, 'Pablo', 'Sanchez', NULL, 'uploads/curriculos/cv_68cdaca32071b_pruebaCV (1).docx', 1, '+53 5 1234567', 'pablo@allnovu.net', '2025-09-19 04:00:00', 'pendiente'),
(4, 'Pablo', 'Sanchez', NULL, 'uploads/curriculos/cv_68cdb18b2c4a6_pruebaCV (1).docx', 1, '+53 5 1237767', 'pablo@allnovu.net', '2025-09-19 04:00:00', 'pendiente'),
(5, 'Pablo', 'Sanchez', NULL, 'uploads/curriculos/cv_68cdb1910ebca_pruebaCV (1).docx', 1, '+53 5 1237767', 'pablo@allnovu.net', '2025-09-19 04:00:00', 'pendiente'),
(6, 'Pablo', 'Sanchez', NULL, 'uploads/curriculos/cv_68cdb1b74036c_pruebaCV (1).docx', 1, '+53 5 1237767', 'pablo@allnovu.net', '2025-09-19 04:00:00', 'pendiente'),
(7, 'Pablo', 'Sanchez', NULL, 'uploads/curriculos/cv_68cdb1d10ee51_pruebaCV (1).docx', 1, '54645656', 'pablo@allnovu.net', '2025-09-19 04:00:00', 'pendiente'),
(8, 'Pablo', 'Sanchez', NULL, 'uploads/curriculos/cv_68cdb255855a7_pruebaCV (1).docx', 1, '55646546', 'pablo@allnovu.net', '2025-09-19 04:00:00', 'pendiente'),
(9, 'Juan', 'Perez Perez', NULL, 'uploads/curriculos/cv_68d3039b2d507_3.pdf', 1, '54673333', 'juanpp@gmail.com', '2025-09-23 04:00:00', 'aprobado'),
(10, 'Juan', 'Perez Perez', NULL, 'uploads/curriculos/cv_68d303a02e6b8_3.pdf', 1, '54673333', 'juanpp@gmail.com', '2025-09-23 04:00:00', 'aprobado'),
(11, 'Juan', 'Perez Perez', NULL, 'uploads/curriculos/cv_68d303a7259d6_3.pdf', 1, '54673333', 'juanpp@gmail.com', '2025-09-23 04:00:00', 'aprobado'),
(12, 'Juan', 'Perez Perez', NULL, 'uploads/curriculos/cv_68d303b45d51b_2.pdf', 1, '54673333', 'juanpp@gmail.com', '2025-09-23 04:00:00', 'aprobado'),
(13, 'Juancc', 'Perez Perez', NULL, 'uploads/curriculos/cv_68d303bc9c987_2.pdf', 1, '54673333', 'juanpp@gmail.com', '2025-09-23 04:00:00', 'aprobado'),
(14, 'Juan', 'Perez Perez', NULL, 'uploads/curriculos/cv_68d30455261a8_2.pdf', 1, '54344567', 'juan@gmail.com', '2025-09-23 04:00:00', 'aprobado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `nombre`, `descripcion`, `salario`) VALUES
(1, 'Tecnico Redes', 'Tecnico Redes', 200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chequeos_medicos`
--

CREATE TABLE `chequeos_medicos` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `fecha_chequeo` date DEFAULT NULL,
  `tipo_chequeo` varchar(100) DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `archivo_informe` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contratos`
--

CREATE TABLE `contratos` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `archivo_contrato` varchar(255) DEFAULT NULL,
  `firma_digital` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contratos`
--

INSERT INTO `contratos` (`id`, `trabajador_id`, `tipo`, `fecha_inicio`, `fecha_fin`, `archivo_contrato`, `firma_digital`) VALUES
(1, 5, 'Contrato por Tiempo Indeterminado', '2025-09-09', NULL, NULL, NULL),
(2, 2, 'Contrato por Tiempo Indeterminado', '2025-09-25', NULL, NULL, NULL),
(3, 1, 'Contrato por Tiempo Determinado', '2025-09-02', NULL, NULL, NULL),
(4, 2, 'Contrato por Tiempo Determinado', '2025-09-24', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `empresa_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id`, `nombre`, `descripcion`, `empresa_id`) VALUES
(1, 'Desarrollo', 'Departamento de Desarrollo', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `deudas_trabajador`
--

CREATE TABLE `deudas_trabajador` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_registro` date DEFAULT NULL,
  `saldada` tinyint(1) DEFAULT NULL,
  `fecha_saldo` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos_trabajador`
--

CREATE TABLE `documentos_trabajador` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `tipo` varchar(100) DEFAULT NULL,
  `archivo` varchar(255) DEFAULT NULL,
  `fecha_upload` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `sector` varchar(255) DEFAULT NULL,
  `tamanno` varchar(255) DEFAULT NULL,
  `estructura` varchar(255) DEFAULT NULL,
  `ubicaciones` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id`, `nombre`, `sector`, `tamanno`, `estructura`, `ubicaciones`) VALUES
(1, 'All Novu', 'prueba', '100', 'prueba', 'prueba');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historico`
--

CREATE TABLE `historico` (
  `id` int(11) NOT NULL,
  `xentity` varchar(20) DEFAULT NULL,
  `xaction` varchar(45) DEFAULT NULL,
  `xid` varchar(20) DEFAULT NULL,
  `xuser` varchar(10) DEFAULT NULL,
  `xdate` datetime DEFAULT NULL,
  `xobs` text DEFAULT NULL,
  `xvisto` varchar(1) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'N',
  `xvisto_date` datetime DEFAULT NULL,
  `xvisto_user` varchar(10) DEFAULT NULL,
  `xip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `historico`
--

INSERT INTO `historico` (`id`, `xentity`, `xaction`, `xid`, `xuser`, `xdate`, `xobs`, `xvisto`, `xvisto_date`, `xvisto_user`, `xip`) VALUES
(1, 'TRABAJADOR', 'UPDATE-TRABAJADOR', NULL, '1', '0000-00-00 00:00:00', 'TRABAJADOR: 1 Pedro Antonio', 'N', NULL, NULL, NULL),
(2, 'TRABAJADOR', 'UPDATE-TRABAJADOR', NULL, '1', '2025-09-29 20:39:43', 'TRABAJADOR: 2 Pablo', 'N', NULL, NULL, NULL),
(6, 'PROGRAMAS-CAPACITACI', 'UPDATE-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 6 Capacitacion de Ruso', 'N', NULL, NULL, NULL),
(8, 'BOLSA_EMPLEO', 'INSERT-POSTULACION', NULL, '1', '0000-00-00 00:00:00', 'POSTULACION: 8 Pablo Sanchez', 'N', NULL, NULL, NULL),
(9, 'TRABAJADOR', 'INSERT-TRABAJADOR', NULL, '1', '0000-00-00 00:00:00', 'TRABAJADOR: 9 Jorge', 'N', NULL, NULL, NULL),
(10, 'TRABAJADOR', 'INSERT-TRABAJADOR', NULL, '1', '0000-00-00 00:00:00', 'TRABAJADOR: 10 Jorgea', 'N', NULL, NULL, NULL),
(11, 'TRABAJADOR', 'INSERT-TRABAJADOR', NULL, '1', '0000-00-00 00:00:00', 'TRABAJADOR: 11 Jorgea', 'N', NULL, NULL, NULL),
(12, 'TRABAJADOR', 'INSERT-TRABAJADOR', NULL, '1', '0000-00-00 00:00:00', 'TRABAJADOR: 12 Jorgea', 'N', NULL, NULL, NULL),
(13, 'BOLSA_EMPLEO', 'INSERT-POSTULACION', NULL, '1', '0000-00-00 00:00:00', 'POSTULACION: 13 Juancc Perez Perez', 'N', NULL, NULL, NULL),
(14, 'BOLSA_EMPLEO', 'INSERT-POSTULACION', NULL, '1', '0000-00-00 00:00:00', 'POSTULACION: 14 Juan Perez Perez', 'N', NULL, NULL, NULL),
(15, 'PROGRAMAS-CAPACITACI', 'INSERT-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 15 Capacitacion de Java 13', 'N', NULL, NULL, NULL),
(16, 'PROGRAMAS-CAPACITACI', 'INSERT-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 16 Capacitacion de Java 12', 'N', NULL, NULL, NULL),
(17, 'PROGRAMAS-CAPACITACI', 'INSERT-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 17 Capacitacion de Java 7', 'N', NULL, NULL, NULL),
(18, 'PROGRAMAS-CAPACITACI', 'INSERT-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 18 Capacitacion de Java 7', 'N', NULL, NULL, NULL),
(19, 'PROGRAMAS-CAPACITACI', 'INSERT-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 19 Capacitacion de Java 7', 'N', NULL, NULL, NULL),
(20, 'PROGRAMAS-CAPACITACI', 'INSERT-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 20 Capacitacion de Java 7', 'N', NULL, NULL, NULL),
(21, 'PROGRAMAS-CAPACITACI', 'INSERT-PROGRAMA', NULL, '1', '0000-00-00 00:00:00', 'PROGRAMA: 21 Capacitacion de Java 7', 'N', NULL, NULL, NULL),
(1526514, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: N', 'N', NULL, NULL, NULL),
(1526515, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: S', 'N', NULL, NULL, NULL),
(1526516, 'TRABAJADORES', 'DEL-TRABAJADORES', '8', '1', '0000-00-00 00:00:00', 'DEL TRABAJADOR: 8', 'N', NULL, NULL, NULL),
(1526517, 'TRABAJADORES', 'DEL-TRABAJADORES', '11', '1', '0000-00-00 00:00:00', 'DEL TRABAJADOR: 11', 'N', NULL, NULL, NULL),
(1526518, 'TRABAJADORES', 'DEL-TRABAJADORES', '10', '1', '0000-00-00 00:00:00', 'DEL TRABAJADOR: 10', 'N', NULL, NULL, NULL),
(1526519, 'TRABAJADORES', 'DEL-TRABAJADORES', '12', '1', '0000-00-00 00:00:00', 'DEL TRABAJADOR: 12', 'N', NULL, NULL, NULL),
(1526520, 'TRABAJADORES', 'DEL-TRABAJADORES', '9', '1', '0000-00-00 00:00:00', 'DEL TRABAJADOR: 9', 'N', NULL, NULL, NULL),
(1526521, 'TRABAJADORES', 'DEL-TRABAJADORES', '7', '1', '0000-00-00 00:00:00', 'BAJA TRABAJADOR: 7 - Fecha: 2025-09-23', 'N', NULL, NULL, NULL),
(1526522, 'USUARIOS', 'CHG-ACTIVO', '86', '1', '0000-00-00 00:00:00', 'USUARIO: 86 Activo: N', 'N', NULL, NULL, NULL),
(1526523, 'USUARIOS', 'CHG-ACTIVO', '86', '1', '0000-00-00 00:00:00', 'USUARIO: 86 Activo: S', 'N', NULL, NULL, NULL),
(1526524, 'SUBCONTRATOS', 'BAJA-SUBCONTRATO', '1', '1', '0000-00-00 00:00:00', 'BAJA SUBCONTRATO: 1 - Fecha: 2025-09-23', 'N', NULL, NULL, NULL),
(1526525, 'PROGRAMAS-CAPACITACI', 'FINALIZAR-PROGRAMA', '1', '1', '0000-00-00 00:00:00', 'FINALIZAR PROGRAMA: 1 - Fecha: 2025-09-24', 'N', NULL, NULL, NULL),
(1526526, 'SUBCONTRATOS', 'BAJA-SUBCONTRATO', '2', '1', '0000-00-00 00:00:00', 'BAJA SUBCONTRATO: 2 - Fecha: 2025-09-24', 'N', NULL, NULL, NULL),
(1526527, 'PROGRAMAS-CAPACITACI', 'FINALIZAR-PROGRAMA', '3', '1', '0000-00-00 00:00:00', 'FINALIZAR PROGRAMA: 3 - Fecha: 2025-09-24', 'N', NULL, NULL, NULL),
(1526528, 'SUBCONTRATOS', 'BAJA-SUBCONTRATO', '3', '1', '0000-00-00 00:00:00', 'BAJA SUBCONTRATO: 3 - Fecha: 2025-09-24', 'N', NULL, NULL, NULL),
(1526529, 'SUBCONTRATOS', 'BAJA-SUBCONTRATO', '7', '1', '0000-00-00 00:00:00', 'BAJA SUBCONTRATO: 7 - Fecha: 2025-09-24', 'N', NULL, NULL, NULL),
(1526530, 'PROGRAMAS-CAPACITACI', 'FINALIZAR-PROGRAMA', '20', '1', '0000-00-00 00:00:00', 'FINALIZAR PROGRAMA: 20 - Fecha: 2025-09-24', 'N', NULL, NULL, NULL),
(1526531, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: N', 'N', NULL, NULL, NULL),
(1526532, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: S', 'N', NULL, NULL, NULL),
(1526533, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: N', 'N', NULL, NULL, NULL),
(1526534, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: S', 'N', NULL, NULL, NULL),
(1526535, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: N', 'N', NULL, NULL, NULL),
(1526536, 'USUARIOS', 'CHG-ACTIVO', '1', '1', '0000-00-00 00:00:00', 'USUARIO: 1 Activo: S', 'N', NULL, NULL, NULL),
(1526537, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '0000-00-00 00:00:00', 'RECURSO: 10 2 portvasos all novu', 'N', NULL, NULL, NULL),
(1526538, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '0000-00-00 00:00:00', 'RECURSO: 11 1 refrigerador solar', 'N', NULL, NULL, NULL),
(1526539, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '2025-09-25 15:41:39', 'RECURSO: 12 1 celular petrolero', 'N', NULL, NULL, NULL),
(1526540, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '0000-00-00 00:00:00', 'RECURSO: 13 5 cafetera all novu', 'N', NULL, NULL, NULL),
(1526541, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '2025-09-25 17:27:36', 'RECURSO: 14 2 ranas', 'N', NULL, NULL, NULL),
(1526542, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '2025-09-25 19:40:29', 'RECURSO: 15 5 celular petrolero 2.0', 'N', NULL, NULL, NULL),
(1526543, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 19:57:06', 'RECURSO: undefined 2 ranassss', 'N', NULL, NULL, NULL),
(1526544, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 19:57:34', 'RECURSO: undefined 5 ranassss', 'N', NULL, NULL, NULL),
(1526545, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 20:03:43', 'RECURSO: undefined 5 celular petroleroffff', 'N', NULL, NULL, NULL),
(1526546, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 20:04:18', 'RECURSO: undefined 5 celular petroleroffff', 'N', NULL, NULL, NULL),
(1526547, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 20:27:32', 'RECURSO: 15 5 celular petrolero 7.0', 'N', NULL, NULL, NULL),
(1526548, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 21:01:59', 'RECURSO: 15 5 celular petrolero 7.0', 'N', NULL, NULL, NULL),
(1526549, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 21:46:23', 'RECURSO: 14 2 ranas ', 'N', NULL, NULL, NULL),
(1526550, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '2025-09-25 21:47:46', 'RECURSO: 16 5  zapatillas all novu', 'N', NULL, NULL, NULL),
(1526551, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 21:48:42', 'RECURSO: 16 5  zapatillas all novu ', 'N', NULL, NULL, NULL),
(1526552, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '2025-09-25 21:50:26', 'RECURSO: 17 2  ventilador', 'N', NULL, NULL, NULL),
(1526553, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '2025-09-25 21:51:26', 'RECURSO: 18 2  ventilador', 'N', NULL, NULL, NULL),
(1526554, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 21:51:35', 'RECURSO: 18 2  ventilador ', 'N', NULL, NULL, NULL),
(1526555, 'RECURSOS', 'INSERT-RECURSO', NULL, '1', '2025-09-25 22:06:54', 'RECURSO: 19 2  zapatillas all novu', 'N', NULL, NULL, NULL),
(1526556, 'RECURSOS', 'UPDATE-RECURSO', NULL, '1', '2025-09-25 22:08:17', 'RECURSO: 19 2  zapatillas all novu ', 'N', NULL, NULL, NULL),
(1526557, 'RECURSOS', 'DEL-RECURSO', '10', '1', '2025-09-26 14:21:24', 'DEL RECURSO: 10', 'N', NULL, NULL, NULL),
(1526558, 'RECURSOS', 'DEL-RECURSO', '8', '1', '2025-09-26 14:22:04', 'DEL RECURSO: 8', 'N', NULL, NULL, NULL),
(1526559, 'RECURSOS', 'DEL-RECURSO', NULL, '1', '2025-09-26 14:26:44', 'DEL RECURSO: 1', 'N', NULL, NULL, NULL),
(1526560, 'RECURSOS', 'DEL-RECURSO', NULL, '1', '2025-09-26 14:35:57', 'DEL RECURSO: 5', 'N', NULL, NULL, NULL),
(1526561, 'RECURSOS', 'DEL-RECURSO', NULL, '1', '2025-09-26 14:36:05', 'DEL RECURSO: 4', 'N', NULL, NULL, NULL),
(1526562, 'RECURSOS', 'DEL-RECURSO', NULL, '1', '2025-09-26 14:39:57', 'DEL RECURSO: 3', 'N', NULL, NULL, NULL),
(1526563, 'RECURSOS', 'DEL-RECURSO', NULL, '1', '2025-09-26 14:42:40', 'DEL RECURSO: 2', 'N', NULL, NULL, NULL),
(1526564, 'TRABAJADOR', 'UPDATE-TRABAJADOR', '2', '1', '2025-09-29 20:59:51', 'TRABAJADOR: 2 Pablo', 'N', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pases_acceso`
--

CREATE TABLE `pases_acceso` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `subcontrato_id` int(11) DEFAULT NULL,
  `areas_acceso` text DEFAULT NULL,
  `fecha_generacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `vigente` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pases_acceso`
--

INSERT INTO `pases_acceso` (`id`, `trabajador_id`, `subcontrato_id`, `areas_acceso`, `fecha_generacion`, `vigente`) VALUES
(2, 1, NULL, 'Desarrollo', '2025-09-18 18:14:05', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_vacaciones`
--

CREATE TABLE `plan_vacaciones` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `anno` int(11) DEFAULT NULL,
  `meses` varchar(100) DEFAULT NULL,
  `dias_autorizados` int(11) DEFAULT NULL,
  `fecha_aprobacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas_capacitacion`
--

CREATE TABLE `programas_capacitacion` (
  `id` int(11) NOT NULL,
  `tema` varchar(255) NOT NULL,
  `dirigido_a` text DEFAULT NULL,
  `responsable` varchar(255) DEFAULT NULL,
  `fecha_estimada` date DEFAULT NULL,
  `fecha_finalizacion` date DEFAULT NULL COMMENT 'Fecha en que se finalizó el programa de capacitación',
  `modalidad` varchar(50) DEFAULT NULL COMMENT 'Modalidad del programa (Presencial, Virtual, Mixta, En línea)',
  `horas` int(3) DEFAULT NULL COMMENT 'Número de horas de duración del programa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `programas_capacitacion`
--

INSERT INTO `programas_capacitacion` (`id`, `tema`, `dirigido_a`, `responsable`, `fecha_estimada`, `fecha_finalizacion`, `modalidad`, `horas`) VALUES
(1, 'Capacitacion de Lenguas Extranjeras', 'Personal operativo', 'Rowder ', '2025-10-31', '2025-09-24', NULL, NULL),
(2, 'Capacitacion de Lenguas Extranjeras', 'Personal operativo', 'Rowder ', '2025-10-31', NULL, NULL, NULL),
(3, 'Capacitacion de Lenguas Extranjeras', 'Personal operativo', 'Rowder ', '2025-10-31', '2025-09-24', NULL, NULL),
(4, 'Capacitacion de Lenguas Extranjeras', 'Todos los trabajadores', 'Rowder ', '2025-10-01', NULL, NULL, NULL),
(5, 'Capacitacion de Lenguas Extranjeras', 'Todos los trabajadores', 'Rowder ', '2025-10-01', NULL, NULL, NULL),
(6, 'Capacitacion de Ruso', 'Todos los trabajadores', 'Rowder ', '2025-11-05', NULL, NULL, NULL),
(7, 'Capacitacion de Java 8', 'Todos los trabajadores', 'Rowder ', '2025-10-02', NULL, 'Presencial', 234),
(8, 'Capacitacion de Java 7', 'Personal operativo', 'Rowder ', '2025-09-12', NULL, 'Presencial', 325),
(9, 'Capacitacion de Java 7', 'Supervisores', 'Rowder ', '2025-09-11', NULL, 'Presencial', 235),
(10, 'Capacitacion de Java 7', 'Directivos', 'Rowder ', '2025-09-12', NULL, 'Virtual', 65),
(11, 'Capacitacion de Java 9', 'Personal operativo', 'Rowder ', '2025-10-09', NULL, 'Presencial', 452),
(12, 'Capacitacion de Java10', 'Personal administrativo', 'Rowder ', '2025-09-10', NULL, 'Presencial', 345),
(13, 'Capacitacion de Ruso', 'Directivos', 'Rowder ', '2025-09-04', NULL, 'Presencial', 54),
(14, 'Capacitacion de Java 12', 'Supervisores', 'Rowder ', '2025-09-18', NULL, 'Virtual', 475),
(15, 'Capacitacion de Java 13', 'Supervisores', 'Rowder ', '2025-09-18', NULL, 'Virtual', 475),
(16, 'Capacitacion de Java 12', 'Supervisores', 'Rowder ', '2025-09-16', NULL, 'Presencial', 543),
(17, 'Capacitacion de Java 7', 'Supervisores', 'Rowder ', '2025-09-04', NULL, 'Presencial', 475),
(18, 'Capacitacion de Java 7', 'Supervisores', 'Rowder ', '2025-10-23', NULL, 'Presencial', 475),
(19, 'Capacitacion de Java 7', 'Directivos', 'Rowder ', '2025-09-03', NULL, 'Presencial', 123),
(20, 'Capacitacion de Java 7', 'Directivos', 'Rowder ', '2025-09-03', '2025-09-24', 'Presencial', 123),
(21, 'Capacitacion de Java 7', 'Carlos Manuel', 'Rowder ', '2025-09-25', NULL, 'Presencial', 90);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recursos`
--

CREATE TABLE `recursos` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `nombre` text DEFAULT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_entrega_a_t` date NOT NULL,
  `fecha_entrega_a_rh` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recursos`
--

INSERT INTO `recursos` (`id`, `trabajador_id`, `nombre`, `estado`, `fecha_entrega_a_t`, `fecha_entrega_a_rh`) VALUES
(6, 5, 'olla all novu', 1, '2025-09-25', NULL),
(7, 5, 'iguana all novu', 1, '2025-09-25', NULL),
(9, 2, 'ventilador 3000', 1, '2025-09-25', NULL),
(11, 1, 'refrigerador solar', 1, '2025-09-25', NULL),
(12, 1, 'celular petrolero', 1, '2025-09-25', NULL),
(13, 5, 'cafetera all novu', 1, '2025-09-25', NULL),
(14, 2, 'ranas ', 0, '2025-09-25', NULL),
(15, 5, 'celular petrolero 7.0', 1, '2025-09-15', NULL),
(16, 5, ' zapatillas all novu ', 0, '2025-09-25', NULL),
(17, 2, ' ventilador', 1, '2025-09-25', NULL),
(18, 2, ' ventilador ', 0, '2025-09-25', '2025-09-25'),
(19, 2, ' zapatillas all novu ', 0, '2025-09-07', '2025-09-25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_asistencia`
--

CREATE TABLE `registro_asistencia` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora_entrada` time DEFAULT NULL,
  `hora_salida` time DEFAULT NULL,
  `ausencia` tinyint(1) DEFAULT NULL,
  `tipo_ausencia` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_capacitacion`
--

CREATE TABLE `registro_capacitacion` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `programa_id` int(11) DEFAULT NULL,
  `fecha_realizacion` date DEFAULT NULL,
  `resultados` text DEFAULT NULL,
  `area` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_vacaciones`
--

CREATE TABLE `registro_vacaciones` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `dias_disfrutados` int(11) DEFAULT NULL,
  `anno` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `xrol_id` tinyint(4) NOT NULL,
  `xrol` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`xrol_id`, `xrol`) VALUES
(1, 'ADMINISTRADOR'),
(2, 'TRABAJADOR'),
(3, 'DESARROLLO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `subcontratos`
--

CREATE TABLE `subcontratos` (
  `id` int(11) NOT NULL,
  `persona_nombre` varchar(255) DEFAULT NULL,
  `carnet_identidad` varchar(11) DEFAULT NULL COMMENT 'Carnet de Identidad (11 dígitos)',
  `estatus` varchar(100) DEFAULT NULL,
  `entidad_representada` varchar(255) DEFAULT NULL,
  `servicio_objeto` text DEFAULT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `areas_acceso` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `subcontratos`
--

INSERT INTO `subcontratos` (`id`, `persona_nombre`, `carnet_identidad`, `estatus`, `entidad_representada`, `servicio_objeto`, `fecha_inicio`, `fecha_fin`, `areas_acceso`) VALUES
(1, 'Ricardo Sanchez Molina', NULL, 'TCP', NULL, 'Es jardinero subcontratado de servicios comunales', '2025-09-17', '2025-09-23', 'jardin'),
(2, 'Heylin Garcia Matos', NULL, 'TCP', NULL, 'Trabaja de Asistencia Técnica en Servicios WEB', '2025-09-18', '2025-09-24', 'jardin'),
(3, 'Heylin Garcia Matos', NULL, 'TCP', NULL, 'Trabaja de Asistencia Técnica en Servicios WEB', '2025-09-18', '2025-09-24', 'jardin'),
(4, 'Heylin Garcia Matos', NULL, 'TCP', NULL, 'Trabaja de Asistencia Técnica en Servicios WEB', '2025-09-18', NULL, 'jardin'),
(5, 'Keyla Dias Lemus', NULL, 'Trabajador', 'Etecsa', 'Reparación de Terminales Telefónicas', '2025-09-26', '2025-10-06', 'Oficina'),
(6, 'Jose Manuel Garcia Rebustillo', NULL, 'TCP', NULL, 'Pintar las paredes de la oficina ', '2025-10-08', '2025-10-11', 'Oficina'),
(7, 'Javier Alcaide Valdez', NULL, 'Trabajador', 'Etecsa', 'Cableado interno para equipo de telecomunicaciones', '2025-10-02', '2025-09-24', 'jardin'),
(8, 'Javier Alcaide Valdez', NULL, 'Trabajador', 'Etecsa', 'Cableado interno para equipo de telecomunicaciones', '2025-10-02', NULL, 'jardin'),
(9, 'Heylin Garcia Matos', NULL, 'TCP', NULL, 'asd asd asd', '2025-09-23', NULL, 'jardin'),
(10, 'Heylin Garcia Matos', NULL, 'TCP', NULL, 'asdasd', '2025-09-10', NULL, 'jardin'),
(11, 'Ricardo Sanchez', '12345678911', 'Trabajador', 'Etecsa', 'servicios comerciales extra', '2025-10-03', NULL, 'Oficina Comercial'),
(12, 'Helen Perez', '98765432101', 'TCP', NULL, 'asdasd', '2025-10-10', NULL, 'Oficina'),
(13, 'Ricardo Sanchez Molina', '85091212345', 'TCP', NULL, 'asdasd', '2025-10-03', NULL, 'Oficina'),
(14, 'Keyla Dias Lemus', '12423423433', 'TCP', NULL, 'asd', '2025-10-07', NULL, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarjetas_snc225`
--

CREATE TABLE `tarjetas_snc225` (
  `id` int(11) NOT NULL,
  `trabajador_id` int(11) DEFAULT NULL,
  `periodo` date DEFAULT NULL,
  `tiempo_trabajo` decimal(10,2) DEFAULT NULL,
  `salarios_devengados` decimal(10,2) DEFAULT NULL,
  `archivo_digital` varchar(255) DEFAULT NULL,
  `fecha_cierre` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `trabajadores`
--

CREATE TABLE `trabajadores` (
  `id` int(11) NOT NULL,
  `cargos_id` int(11) DEFAULT NULL,
  `departamento_id` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `huella_dactilar` varchar(255) DEFAULT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellidos` varchar(255) NOT NULL,
  `carnet_identidad` varchar(50) NOT NULL,
  `sexo` varchar(50) DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(50) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `nivel_educacional` varchar(100) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  `fecha_baja` date DEFAULT NULL,
  `estatus` varchar(100) DEFAULT NULL,
  `bolsa_empleo_id` int(11) DEFAULT NULL,
  `trabajador_eliminado` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `trabajadores`
--

INSERT INTO `trabajadores` (`id`, `cargos_id`, `departamento_id`, `foto`, `huella_dactilar`, `nombre`, `apellidos`, `carnet_identidad`, `sexo`, `edad`, `direccion`, `telefono`, `email`, `nivel_educacional`, `fecha_contratacion`, `fecha_baja`, `estatus`, `bolsa_empleo_id`, `trabajador_eliminado`) VALUES
(1, 1, NULL, 'uploads/trabajadores/foto_68d686d74e7ef_av3.png', 'HUELLA123456789', 'Pedro Antonio', 'Manduley Roca', '85091212345', 'M', 40, 'Calle 23 #456, Vedado, La Habana', '+53 5 1234567', 'pedro.mandulsy@example.com', 'Universitario', '2020-03-15', NULL, 'activo', 10, 0),
(2, 1, 1, '', NULL, 'Pablo', 'Sanchez', '12345678911', 'M', 56, 'calle 654 entre 3ra y 5ta', '53453232', 'pabl4o@allnovu.net', 'Universitario', '2025-09-17', NULL, 'activo', 1, 0),
(5, 1, NULL, 'uploads/trabajadores/foto_68cc21fdbb7d5_vector-de-perfil-avatar-predeterminado-foto-usuario-medios-sociales-icono-183042379.webp', NULL, 'Carlos', 'Manuel', '3212554', 'M', 32, 'calle 455 43 3ser', '5466447', 'pablo@allnovu.net', 'Universitario', '2025-09-16', NULL, 'activo', 1, 0),
(6, 1, NULL, 'uploads/trabajadores/foto_68cc228590399_vector-de-perfil-avatar-predeterminado-foto-usuario-medios-sociales-icono-183042379.webp', NULL, 'Rafael', 'Garcia', '5323432', 'M', 34, 'calle 222 entre 5ta y 7ma', '533424234', 'rafael@allnovu.net', 'Universitario', '2025-09-16', NULL, 'activo', 1, 1),
(7, 1, NULL, 'uploads/trabajadores/foto_68cc28c1202c7_vector-de-perfil-avatar-predeterminado-foto-usuario-medios-sociales-icono-183042379.webp', NULL, 'Jhonny', 'Rodry', '45345345345', 'M', 24, '345345345', '3453453454', 'jhonny@asdas', 'Universitario', '2025-09-15', '2025-09-23', 'activo', 1, 1),
(8, 1, NULL, '', NULL, 'Luis ', 'p', '01032963698', 'F', 89, 'lo', '5588963214', 'lola@k.com', 'bajo', '2025-09-23', NULL, 'activo', 2, 1),
(9, 1, NULL, 'uploads/trabajadores/foto_68d2a88e3aa47_Screenshot 2025-09-18 100848.png', NULL, 'Jorge', 'Ramirez', '01022268102', 'M', 24, 'calle 654 entre 3ra y 5ta', '7564454', 'jorge@gmail.com', 'Universitario', '2025-09-12', NULL, 'activo', 3, 1),
(10, 1, NULL, 'uploads/trabajadores/foto_68d2a8cae7df4_Screenshot 2025-09-18 100848.png', NULL, 'Jorgea', 'Ramirez', '01022268104', 'M', 24, 'calle 654 entre 3ra y 5ta', '7564454', 'jorge@gmail.com', 'Universitario', '2025-09-12', NULL, 'activo', 3, 1),
(11, 1, NULL, 'uploads/trabajadores/foto_68d2a8dc2f12d_Screenshot 2025-09-18 100848.png', NULL, 'Jorgea', 'Ramirez', '01022268122', 'M', 24, 'calle 654 entre 3ra y 5ta', '7564454', 'jorge@gmail.com', 'Universitario', '2025-09-12', NULL, 'activo', 3, 1),
(12, 1, NULL, 'uploads/trabajadores/foto_68d2a94aca02f_Screenshot 2025-09-18 100848.png', NULL, 'Jorgeaaa', 'Ramirez', '01022268141', 'M', 24, 'calle 654 entre 3ra y 5ta', '7564454', 'jorge@gmail.com', 'Universitario', '2025-09-12', NULL, 'activo', 3, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `xusuario_id` int(11) NOT NULL,
  `xusuario` varchar(120) DEFAULT NULL,
  `xemail` varchar(100) DEFAULT NULL,
  `xpwd` varchar(100) DEFAULT NULL,
  `xnif` varchar(20) DEFAULT NULL,
  `xmovil` varchar(20) DEFAULT NULL,
  `xtelefono` varchar(20) DEFAULT NULL,
  `xdomicilio` varchar(100) DEFAULT NULL,
  `xcod_postal` varchar(5) DEFAULT NULL,
  `xmunicipio_id` int(11) DEFAULT 0,
  `xmunicipio` varchar(70) DEFAULT NULL,
  `xprovincia_id` int(11) DEFAULT 0,
  `xpais_id` int(11) DEFAULT 0,
  `xobs` varchar(255) DEFAULT NULL,
  `xuseralta_id` int(11) DEFAULT NULL,
  `xusermodif_id` int(11) DEFAULT NULL,
  `xdatealta` datetime DEFAULT NULL,
  `xdatemodif` datetime DEFAULT NULL,
  `xactivo` varchar(1) DEFAULT 'S',
  `xult_acceso` datetime DEFAULT NULL,
  `xrol_id` tinyint(4) DEFAULT NULL,
  `xeliminado` tinyint(4) DEFAULT 0,
  `xhash` varchar(100) DEFAULT NULL,
  `xver_locales` varchar(1) DEFAULT 'N',
  `xver_trabajadores` varchar(1) DEFAULT 'N'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`xusuario_id`, `xusuario`, `xemail`, `xpwd`, `xnif`, `xmovil`, `xtelefono`, `xdomicilio`, `xcod_postal`, `xmunicipio_id`, `xmunicipio`, `xprovincia_id`, `xpais_id`, `xobs`, `xuseralta_id`, `xusermodif_id`, `xdatealta`, `xdatemodif`, `xactivo`, `xult_acceso`, `xrol_id`, `xeliminado`, `xhash`, `xver_locales`, `xver_trabajadores`) VALUES
(1, 'Luis Ramón (dev)', 'luisr@allnovu.net', '2025', NULL, '', NULL, NULL, NULL, 0, NULL, 0, 0, 'asd', 6, 1, '2025-08-26 09:32:57', '0000-00-00 00:00:00', 'S', '2025-09-30 15:10:59', 1, 0, 'hUYlYTJEG325980tebYK39z26o1C4QM2oP72OMDiprWex7xoc6B2uNbLTAAYtHaujYUjxYEDcHROK7Toj3I2DbejHn7FuhQ6Ksu1', 'S', 'S'),
(76, 'Pedro Antonio', 'pedrom@allnovu.net', '$2y$10$6j1VVe27JH/vEHi7jfmveuop.Un88uPCwuPL/gXSL6GeKt6/BXhZO', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 'asd', 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'S', NULL, 1, 0, 'zZj7a8gvq5uaTpKT5IFZWCpVRCn3Ydzi1ntdhskPDtzoeU0u9IMQj5AQsQbWCEHkPF0A250cKq6VJRzPAlsFPSVvSsf3OE3TXkrs', 'N', 'N'),
(85, 'Prueba Desarrollador', 'desarrollo@allnovu.net', '$2y$10$EAvrptfxfBTdwIsu0dwUNuplBCzVAsrTbb2VyKjB3GkKFbS.9J3n6', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 'Este es un Usuario de Prueba Desarrollador', 1, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'S', '0000-00-00 00:00:00', 3, 0, 'Lsy2sp20JMa4ISSOhWZcRcwtGq79H3XBW5ENPxV6GDhmNpaA1oDhASGj5JdbW4Rc6rcChXdSYO4bId0ce0UkubxtXaBTlrpKiTNa', 'N', 'N'),
(86, 'Trabajador Prueba', 'trabajador@allnovu.net', '$2y$10$m4bUkZMBBmk/7s5OgJnxIe1jSii6g0jtTYjXc6TzgFMFAaIM2D.je', NULL, NULL, NULL, NULL, NULL, 0, NULL, 0, 0, 'prueba de trabajador', 85, 1, '0000-00-00 00:00:00', '0000-00-00 00:00:00', 'S', '0000-00-00 00:00:00', 2, 0, 'sYvDbd4uPpL5FRfMCM6Dtuc2eP99e5Y0idEGCcK8ViGptRdbsDMstMZBTvlSGTgMnQouuGS2G2fxTRY1Z5LakDWikSTM0poZqwJu', 'N', 'N');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `accesos_informaticos`
--
ALTER TABLE `accesos_informaticos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `ayudas`
--
ALTER TABLE `ayudas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `bolsa_empleo`
--
ALTER TABLE `bolsa_empleo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ci_bolsa_empleo` (`ci_bolsa_empleo`),
  ADD KEY `cargo_postulado_id` (`cargo_postulado_id`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `chequeos_medicos`
--
ALTER TABLE `chequeos_medicos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `deudas_trabajador`
--
ALTER TABLE `deudas_trabajador`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `documentos_trabajador`
--
ALTER TABLE `documentos_trabajador`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historico`
--
ALTER TABLE `historico`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pases_acceso`
--
ALTER TABLE `pases_acceso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`),
  ADD KEY `subcontrato_id` (`subcontrato_id`);

--
-- Indices de la tabla `plan_vacaciones`
--
ALTER TABLE `plan_vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `programas_capacitacion`
--
ALTER TABLE `programas_capacitacion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recursos`
--
ALTER TABLE `recursos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `registro_capacitacion`
--
ALTER TABLE `registro_capacitacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`),
  ADD KEY `programa_id` (`programa_id`);

--
-- Indices de la tabla `registro_vacaciones`
--
ALTER TABLE `registro_vacaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`xrol_id`);

--
-- Indices de la tabla `subcontratos`
--
ALTER TABLE `subcontratos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ux_subcontratos_ci` (`carnet_identidad`);

--
-- Indices de la tabla `tarjetas_snc225`
--
ALTER TABLE `tarjetas_snc225`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trabajador_id` (`trabajador_id`);

--
-- Indices de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carnet_identidad` (`carnet_identidad`),
  ADD KEY `cargos_id` (`cargos_id`),
  ADD KEY `bolsa_empleo_id` (`bolsa_empleo_id`),
  ADD KEY `idx_departamento_id` (`departamento_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`xusuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `accesos_informaticos`
--
ALTER TABLE `accesos_informaticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ayudas`
--
ALTER TABLE `ayudas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bolsa_empleo`
--
ALTER TABLE `bolsa_empleo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `chequeos_medicos`
--
ALTER TABLE `chequeos_medicos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `contratos`
--
ALTER TABLE `contratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `deudas_trabajador`
--
ALTER TABLE `deudas_trabajador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `documentos_trabajador`
--
ALTER TABLE `documentos_trabajador`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `historico`
--
ALTER TABLE `historico`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1526565;

--
-- AUTO_INCREMENT de la tabla `pases_acceso`
--
ALTER TABLE `pases_acceso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `plan_vacaciones`
--
ALTER TABLE `plan_vacaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `programas_capacitacion`
--
ALTER TABLE `programas_capacitacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `recursos`
--
ALTER TABLE `recursos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_capacitacion`
--
ALTER TABLE `registro_capacitacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `registro_vacaciones`
--
ALTER TABLE `registro_vacaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `subcontratos`
--
ALTER TABLE `subcontratos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `tarjetas_snc225`
--
ALTER TABLE `tarjetas_snc225`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `xusuario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=87;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `accesos_informaticos`
--
ALTER TABLE `accesos_informaticos`
  ADD CONSTRAINT `accesos_informaticos_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `ayudas`
--
ALTER TABLE `ayudas`
  ADD CONSTRAINT `ayudas_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `bancos`
--
ALTER TABLE `bancos`
  ADD CONSTRAINT `bancos_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `bolsa_empleo`
--
ALTER TABLE `bolsa_empleo`
  ADD CONSTRAINT `bolsa_empleo_ibfk_1` FOREIGN KEY (`cargo_postulado_id`) REFERENCES `cargos` (`id`);

--
-- Filtros para la tabla `chequeos_medicos`
--
ALTER TABLE `chequeos_medicos`
  ADD CONSTRAINT `chequeos_medicos_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `contratos`
--
ALTER TABLE `contratos`
  ADD CONSTRAINT `contratos_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD CONSTRAINT `departamentos_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresa` (`id`);

--
-- Filtros para la tabla `deudas_trabajador`
--
ALTER TABLE `deudas_trabajador`
  ADD CONSTRAINT `deudas_trabajador_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `documentos_trabajador`
--
ALTER TABLE `documentos_trabajador`
  ADD CONSTRAINT `documentos_trabajador_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `pases_acceso`
--
ALTER TABLE `pases_acceso`
  ADD CONSTRAINT `pases_acceso_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`),
  ADD CONSTRAINT `pases_acceso_ibfk_2` FOREIGN KEY (`subcontrato_id`) REFERENCES `subcontratos` (`id`);

--
-- Filtros para la tabla `plan_vacaciones`
--
ALTER TABLE `plan_vacaciones`
  ADD CONSTRAINT `plan_vacaciones_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `recursos`
--
ALTER TABLE `recursos`
  ADD CONSTRAINT `recursos_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `registro_asistencia`
--
ALTER TABLE `registro_asistencia`
  ADD CONSTRAINT `registro_asistencia_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `registro_capacitacion`
--
ALTER TABLE `registro_capacitacion`
  ADD CONSTRAINT `registro_capacitacion_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`),
  ADD CONSTRAINT `registro_capacitacion_ibfk_2` FOREIGN KEY (`programa_id`) REFERENCES `programas_capacitacion` (`id`);

--
-- Filtros para la tabla `registro_vacaciones`
--
ALTER TABLE `registro_vacaciones`
  ADD CONSTRAINT `registro_vacaciones_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `tarjetas_snc225`
--
ALTER TABLE `tarjetas_snc225`
  ADD CONSTRAINT `tarjetas_snc225_ibfk_1` FOREIGN KEY (`trabajador_id`) REFERENCES `trabajadores` (`id`);

--
-- Filtros para la tabla `trabajadores`
--
ALTER TABLE `trabajadores`
  ADD CONSTRAINT `fk_trabajadores_departamento` FOREIGN KEY (`departamento_id`) REFERENCES `departamentos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `trabajadores_ibfk_1` FOREIGN KEY (`cargos_id`) REFERENCES `cargos` (`id`),
  ADD CONSTRAINT `trabajadores_ibfk_2` FOREIGN KEY (`bolsa_empleo_id`) REFERENCES `bolsa_empleo` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
