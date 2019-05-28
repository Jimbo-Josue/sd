<?php

class chats
{
	const ESTADO_ERROR = 3;	
	const ESTADO_EXITO = 2;	
	const USUARIO = 1;
	const CONTACTO = 0;

	public function post($peticion)
	{
		$body = file_get_contents('php://input');
		$chat = json_decode($body); 
		$resultado = self::crear_chat($chat);
		switch($resultado)
		{
			case self::ESTADO_ERROR:
		 		http_response_code(400);
				return
					[
						"estado"=>"400",
						"mensaje"=>"No tienes permiso para enviar el mensaje"
			
					];
				break;
			case self::ESTADO_EXITO:
				http_response_code(200);
				return
					[
						"estado"=>"200",
						"mensaje"=>"mensaje enviado correctamente"
					];
				break;
		}
	}

	private function crear_chat($peticion)
	{		
		$mensaje = $peticion->mensaje;
		$sala = self::obtenerSala($peticion);

		if($sala[0] == -1)
		{
			return self::ESTADO_ERROR;
		}
		
		$comando = "INSERT INTO chat (idsala,mensaje,estado) VALUES (".$sala[0].",'".$mensaje."',".$sala[1].")";
		$pdo = ConexionBD::obtenerInstancia()->obtenerBD();
		$sentencia = $pdo->prepare($comando);

		$resultado = $sentencia->execute();

        	if ($resultado) {
           	return self::ESTADO_EXITO;
        	} else {
           	return self::ESTADO_ERROR;
        	}
	}
	
	private function obtenerSala($peticion)
	{
		$c = apache_request_headers();
		
		if(isset($c["Authorization"]))
		{
			$clave = $c["Authorization"];
			$comando = "SELECT COUNT(*) FROM sala WHERE claveApi='".$clave."'";
			$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
            		$sentencia->execute();
            		if($sentencia->fetchColumn() > 0)
			{
				$comando2 = "SELECT idSala from sala WHERE claveApi='".$clave."'";
            			$sentencia2 = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando2);
            			$sentencia2->execute();				
				return [$sentencia2->fetchColumn(), self::CONTACTO];
			}
			else
			{
				$idUsuario = usuarios::autorizar();
				$idContacto = $peticion->idContacto;

				$comando = "SELECT COUNT(*) FROM sala WHERE idUsuario=".$idUsuario." and idContacto=".$idContacto;
	         		$sentencia = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando);
    	        		$sentencia->execute();
        	    		if($sentencia->fetchColumn() > 0)
				{
	                		$comando2 = "SELECT idSala FROM sala WHERE idUsuario=".$idUsuario." and idContacto=".$idContacto;
    	            			$sentencia2 = ConexionBD::obtenerInstancia()->obtenerBD()->prepare($comando2);
        	        		$sentencia2->execute();
					return [$sentencia2->fetchColumn(), self::USUARIO];
				}
			}
		}
		else
		{
			return [-1,-1];
		}
		return [-1, -1];
	}
}
