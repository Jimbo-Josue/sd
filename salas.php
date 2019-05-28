<?php

// Authorization = CLAVE API
// ID_contacto => body

class salas
{
	const ESTADO_ERROR = 1;
	const ESTADO_EXITO = 2;

	public static function post($peticion)
	{	
		$idUsuario = usuarios::autorizar();
		
		$body = file_get_contents('php://input');
		$data = json_decode($body);

		$idContacto = $data->idContacto;
		
		$respuesta = self::registrar_sala($idUsuario, $idContacto);

		switch($respuesta)
		{
			case self::ESTADO_ERROR:
				http_response_code(400);
				return 
				[
					"estado" => "600",
					"mensaje" => "Error al crear la sala"
				];
				break;
			case self::ESTADO_EXITO:
				
				return
				[
					"estado" => "200",
					"mensaje" => "Sala creada"
				];
				break;
			default:
				return
				[
					"estado" => "201",
					"mensaje" => $respuesta
				];

		}
	}
	public function registrar_sala($idUsuario, $idContacto)
	{
		try
		{
			if(self::existe($idUsuario, $idContacto) == self::ESTADO_ERROR)
			{
				return self::ESTADO_ERROR;
			}
			
			// Comprobar de que no exita copias

			$comando2 = "SELECT COUNT(*) FROM sala WHERE idUsuario=". $idUsuario ." and idContacto=" .$idContacto;
			$sentencia2 = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando2);
			$sentencia2->execute();
			
			if($sentencia2->fetchColumn() > 0)
			{
				return self::ESTADO_ERROR;
			}
			else
			{
				// Insertamos
				$claveApi=usuarios::generarClaveApi();
				$comando3 = "INSERT INTO sala (idUsuario,idContacto,claveApi) VALUES (".$idUsuario.",".$idContacto.",'".$claveApi."')";
				$pdo = ConexionBD::obtenerInstancia()->obtenerBD();
				$sentencia3 = $pdo->prepare($comando3);

				$resultado = $sentencia3->execute();

				if($resultado)
				{
					return self::ESTADO_EXITO;
				}
				else
				{
					return self::ESTADO_ERROR;
				}
			}
		}
		catch(PDOException $e)
		{
			return self::ESTADO_ERROR;
		}
		return self::ESTADO_ERROR;
	}

	public function existe($idUsuario, $idContacto)
	{
            $comando = "SELECT COUNT(*) FROM contacto WHERE contacto.idUsuario=".$idUsuario." and contacto.idContacto=".$idContacto;
            $sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            $sentencia->execute();
	          if($sentencia->fetchColumn() > 0)
            {
                return self::ESTADO_EXITO;
            }
	    return self::ESTADO_ERROR;
	}

}
