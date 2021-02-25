<?php


	namespace App\Models;

	use MF\Model\Model;

	class Usuario extends Model {
		private $id;
		private $nome;
		private $email;
		private $senha;


		public function __get($atr) {
			return $this->$atr;
		}
		public function __set($atr, $valor) {
			$this->$atr= $valor;
		}

		// salvar

		public function salvar() {
			$query= "insert into usuarios(nome, email, senha) values(:nome, :email, :senha)";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':nome',$this->__get('nome'));
			$stmt->bindValue(':email',$this->__get('email'));
			$stmt->bindValue(':senha',$this->__get('senha')); // md5() -> hash 32 caractrs

			$stmt->execute();


			return $this;
		}


		// validar se um cadastro pode ser feito
		public function validarCadastro() {
			$valido= true;

			if (strlen($this->__get('nome')) < 3) {
				$valido= false;
			}
			if (strlen($this->__get('email')) < 3) {
				$valido= false;
			}
			if (strlen($this->__get('senha')) < 3) {
				$valido= false;
			}
			

			return $valido;
		}


		// recuperar usuario por e-mail

		public function getUsuarioPorEmail() {
			$query= "select nome, email from usuarios where email = :email";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':email',$this->__get('email'));

			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);


		}


		// autenticar

		public function autenticar() {
			$query= "select id, nome, email from usuarios where email = :email and senha = :senha";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':email',$this->__get('email'));
			$stmt->bindValue(':senha',$this->__get('senha'));

			$stmt->execute();

			$usuario= $stmt->fetch(\PDO::FETCH_ASSOC);


			if (isset($usuario['id']) && $usuario['id'] != '' && isset($usuario['nome']) && $usuario['nome'] != '') {
				$this->__set('id', $usuario['id']);
				$this->__set('nome', $usuario['nome']);
			}

			return $this;

		}

		public function getAll() {

			$query= "
				select
					u.nome, 
					u.id, 
					u.email, 
					(
						select
							count(*)
						from
							usuarios_seguidores as us
						where
							us.id_usuario = :id_usuario and us.id_usuario_seguindo = u.id
					) as seguindo_sn
				from 
					usuarios as u
				where 
					u.nome like :nome and u.id != :id_usuario";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':nome','%'.$this->__get('nome').'%');
			$stmt->bindValue(':id_usuario',$this->__get('id'));
			$stmt->execute();

			return $stmt->fetchAll(\PDO::FETCH_ASSOC);
		}

		public function seguirUsuario($id_usuario_seguindo) {
			$query= "insert into usuarios_seguidores(id_usuario, id_usuario_seguindo)values(:id_usuario, :id_usuario_seguindo)";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':id_usuario', $this->__get('id'));
			$stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
			$stmt->execute();

			echo "seguindo";

			return true;

		}
		public function deixarSeguirUsuario($id_usuario_seguindo) {
			$query= "delete from usuarios_seguidores where id_usuario = :id_usuario and id_usuario_seguindo = :id_usuario_seguindo";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':id_usuario', $this->__get('id'));
			$stmt->bindValue(':id_usuario_seguindo', $id_usuario_seguindo);
			$stmt->execute();

			echo "Deixar Seguir user";
			return true;
		}

		// informações de usuario
		public function getInfoUsuario() {
			$query= "select nome from usuarios where id = :id_usuario";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':id_usuario', $this->__get('id'));
			$stmt->execute();

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		// total de tweets
		public function getTotalTweets() {
			$query= "select count(*) as total_tweet from tweets where id_usuario = :id_usuario";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':id_usuario', $this->__get('id'));
			$stmt->execute();

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		// total de usuarios que estamos seguindo
		public function getTotalSeguindo() {
			$query= "select count(*) as total_seguindo from usuarios_seguidores where id_usuario = :id_usuario";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':id_usuario', $this->__get('id'));
			$stmt->execute();

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}

		// total de seguidores
		public function getTotalSeguidores() {
			$query= "select count(*) as total_seguidores from usuarios_seguidores where id_usuario_seguindo = :id_usuario";
			$stmt= $this->db->prepare($query);
			$stmt->bindValue(':id_usuario', $this->__get('id'));
			$stmt->execute();

			return $stmt->fetch(\PDO::FETCH_ASSOC);
		}




	}




?>