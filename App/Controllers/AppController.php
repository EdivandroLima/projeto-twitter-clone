<?php

namespace App\Controllers;


//os recursos do miniframework
use MF\Controller\Action;
use MF\Model\Container;


class AppController extends Action {

	public function timeline() {

		$this->validaAutenticacao();

		// recuperar tweets
		$tweet= Container::getModel('Tweet');
		$tweet->__set('id_usuario', $_SESSION['id']);

		// variáveis de páginação
		$total_registros_pagina= 10;
		$pagina= (int) (isset($_GET['pagina'])?$_GET['pagina']:1);
		$deslocamento= ($pagina - 1) * $total_registros_pagina;

		$tweets= $tweet->getPorPagina($total_registros_pagina, $deslocamento);

		$total_tweets= $tweet->getTotalRegistros();


		$this->view->total_de_paginas= ceil($total_tweets['total'] / $total_registros_pagina);
		$this->view->pagina_ativa= $pagina;


		$this->view->tweets= $tweets;


		$usuario_logado= container::getModel('Usuario');
		$usuario_logado->__set('id', $_SESSION['id']);


		$this->view->info_usuario= $usuario_logado->getInfoUsuario();
		$this->view->total_tweets= $usuario_logado->getTotalTweets();
		$this->view->total_seguindo= $usuario_logado->getTotalSeguindo();
		$this->view->total_seguidores= $usuario_logado->getTotalSeguidores(); 

		$this->render('timeline');
	
	}

	public function tweet() {

		$this->validaAutenticacao();


		$tweet= Container::getModel('Tweet');

		$tweet->__set('tweet', $_POST['tweet']);
		$tweet->__set('id_usuario', $_SESSION['id']);
		$tweet->salvar();
		header('Location: /timeline');
		
	}

	public function quemSeguir() {
		$this->validaAutenticacao();

		$pesquisarPor= isset($_GET['pesquisarPor'])? $_GET['pesquisarPor']:'';


		$usuarios= array();

		if ($pesquisarPor != '') {
			$usuario= Container::getModel('Usuario');
			$usuario->__set('nome', $pesquisarPor);
			$usuario->__set('id', $_SESSION['id']);

			$usuarios= $usuario->getAll();

		}

		$this->view->usuarios= $usuarios;


		$usuario_logado= container::getModel('Usuario');
		$usuario_logado->__set('id', $_SESSION['id']);


		$this->view->info_usuario= $usuario_logado->getInfoUsuario();
		$this->view->total_tweets= $usuario_logado->getTotalTweets();
		$this->view->total_seguindo= $usuario_logado->getTotalSeguindo();
		$this->view->total_seguidores= $usuario_logado->getTotalSeguidores(); 




		$this->render('quemSeguir');
		
	}


	public function validaAutenticacao() {

		session_start();

		if (!isset($_SESSION['id']) || $_SESSION['id'] == '' || !isset($_SESSION['nome']) || $_SESSION['nome'] == '') {
			header('Location: /?login=erro');
		}
		
	}


	public function acao() {
		$this->validaAutenticacao();

		$acao= isset($_GET['acao'])?$_GET['acao']:'';
		$id_usuario_seguindo= isset($_GET['id_usuario'])?$_GET['id_usuario']:'';

		$usuario= container::getModel('Usuario');
		$usuario->__set('id', $_SESSION['id']);


		if ($acao == 'seguir') {
			$usuario->seguirUsuario($id_usuario_seguindo);
			
			header('Location: /quem_seguir');

		} else if ($acao == 'deixar_de_seguir') {
			$usuario->deixarSeguirUsuario($id_usuario_seguindo);

			header('Location: /quem_seguir');

		}

	}

	public function remover_tweet() {
		$this->validaAutenticacao();
		
		$tweet= container::getModel('Tweet');
		$tweet->__set('id', $_POST['id_tweet']);
		$tweet->remover();

		header('Location: /timeline');


	}

	

}

?>