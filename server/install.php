<?php
/**
*  Copyright 2015 TheShark34 & Vavaballz
*  This file is part of S-Update.
*  S-Update is free software: you can redistribute it and/or modify
*  it under the terms of the GNU Lesser General Public License as published by
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*  S-Update is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU Lesser General Public License for more details.
*  You should have received a copy of the GNU Lesser General Public License
*  along with S-Update.  If not, see <http://www.gnu.org/licenses/>.
*/

// Si le formulaire est bien envoyé
if(isset($_POST)){
	// Si tous les champs sauf le mot de passe sont bien remplis
	if(!empty($_POST['host']) && !empty($_POST['username']) && !empty($_POST['dbname']) && !empty($_POST['ownername'])){
		// Essais de connexion à la base de données
		try{
			$pdo = new PDO('mysql:dbname='. $_POST['dbname'] .';host='. $_POST['host'] .'', $_POST['username'], $_POST['password']);
			$failed = false;
		}catch(PDOException $e){
			$failed = true;
			$notif = ['type' => 'danger', 'msg' => 'Impossible de se connecter à la base de données !'];
		}
		// Si la connexion fonctionne
		if(!$failed){
			// Vérifie si la table existe déjà
			$exist = $pdo->prepare("SHOW TABLES LIKE 'openauth_users'");
			// Si elle n'existe pas, on la créer
		    if($exist->rowCount()==0){
				$req = $pdo->prepare('
					SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
					SET time_zone = "+00:00";

					/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
					/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
					/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
					/*!40101 SET NAMES utf8 */;

					CREATE TABLE IF NOT EXISTS `openauth_users` (
					  `id` int(11) NOT NULL AUTO_INCREMENT,
					  `username` varchar(255) NOT NULL,
					  `password` varchar(255) NOT NULL,
					  `GUID` varchar(255) NOT NULL,
					  `UUID` varchar(255) NOT NULL,
					  `accessToken` varchar(255) NOT NULL,
					  `clientToken` varchar(255) NOT NULL,
					  PRIMARY KEY (`id`),
					  UNIQUE KEY `UUID` (`GUID`)
					) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

					/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
					/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
					/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
				');
				$req->execute();
		    }
		    // Récupération de la base du fichier de config
			$config_file = file('config_base.php');
			// Parcour du fichier
			foreach($config_file as $k=>$v){
				// Modification de l' "owner"
				if(strpos($v, "'owner' => ''")){
					$config_file[$k] = "\t\t'owner' => '{$_POST['ownername']}',\n";
				}
				// Modification de "database"
				if(strpos($v, "'database' => ''")){
					$config_file[$k] = "\t\t'database' => '{$_POST['dbname']}',\n";
				}
				// Modification de "host"
				if(strpos($v, "'host' =>")){
					$config_file[$k] = "\t\t'host' => '{$_POST['host']}',\n";
				}
				// Modification de "username"
				if(strpos($v, "'username' =>")){
					$config_file[$k] = "\t\t'username' => '{$_POST['username']}',\n";
				}
				// Modification de "password"
				if(strpos($v, "'password' =>")){
					$config_file[$k] = "\t\t'password' => '{$_POST['password']}',\n";
				}
			}
			// Enregistrement dans le fichier config.php
			file_put_contents('config.php', $config_file);
			// On rafraichit la page
			echo "<meta http-equiv='refresh' content='0'>";
		}
	}
}

?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>OpenAuth Configuration</title>

        <!-- Bootstrap -->
        <link href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" rel="stylesheet">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
          <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
          <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->

        <link href="http://theshark34.github.io/OpenAuth-Server/style.css" rel="stylesheet">
        <style>
		.bg-danger{
		  width: 600px;
		  padding: 5px;
		  margin: auto;
		  color: black;
		  background-color: #f2dede;
		}
        </style>
    </head>

    <body>
        <div class="fulldiv classic">
            <img src="http://theshark34.github.io/OpenAuth-Server/logo.png" />

            <h1>Configuration</h1>
            <br />
            <p class="marged-paragraph">
                Bienvenue dans la configuration de votre serveur OpenAuth.
                <br/>
                Nous allons maintenant configurer les identifiants de connexion à votre base Mysql.
                <br />
                Alors, qu'attendez vous ? !

                <br /><br /><br />
                <?php
                // Affichage des erreurs si il y en a
                ?>
				<p class="bg-<?= isset($notif) ? $notif['type'] : "warning" ?>"><?= isset($notif) ? $notif['msg'] : "" ?></p>
                <form method="post" action="">
                	<h1><u><b>Base de données</b></u></h1>
                    <label for="username">Hôte</label> : <input class="text-field" type="text" name="host" id="host" placeholder="Exemple: localhost" required/>
                    <br />
                    <label for="username">Nom l'utilisateur</label> : <input class="text-field" type="text" name="username" id="username" placeholder="Exemple: root"  required/>
                    <br />
                    <label for="password">Mot de Passe</label> : <input class="text-field" type="password" name="password" id="password"/>
                    <br />
                    <label for="redirecturl">Base de données</label> : <input class="text-field" type="text" name="dbname" id="dbname" placeholder="Exemple: openauth"  required/>
                    <br />

                	<h1><u><b>Infos du serveur</b></u></h1>
                    <label for="username">Owner</label> : <input class="text-field" type="text" name="ownername" id="ownername" placeholder="Votre nom" required/>
                    <br />
                    <input class="submit-button" type="submit" value="Appliquer" />
                </form>
            </p>
        </div>

        <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <!-- Include all compiled plugins (below), or include individual files as needed -->
        <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </body>
</html>