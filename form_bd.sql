SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE IF NOT EXISTS `form` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `NOM` varchar(255) NOT NULL,
  `TITRE` varchar(255) NOT NULL,
  `INC_FORM_VALIDATE` varchar(255) NOT NULL,
  `INC_MAIL` varchar(255) NOT NULL,
  `INC_PASS` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- ID : clé primaire.
-- NOM : nom du formulaire pour l'attr name du <form>.
-- TITRE : titre affiché dans le formulaire.
-- INC_FORM_VALIDATE : "db" ou "email" choix de l'utilisateur pour les résultats du formulaire.
-- INC_MAIL : email de l'utilisateur pour les envois de résultat et / ou pour la connexion ultérieure.
-- INC_PASS : password de l'utilisateur pour la connexion ultérieure.

CREATE TABLE IF NOT EXISTS `form_item` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_FORM` int(11) NOT NULL,
  `NOM` varchar(255) NOT NULL,
  `TITRE` varchar(255) NOT NULL,
  `TYPE` varchar(255) NOT NULL,
  `NOTE` varchar(255) NOT NULL,
  `REQUIRED` varchar(3) NOT NULL DEFAULT 'NON',
  `ERROR` varchar(255) NOT NULL,
  `VALUE` longtext NOT NULL,
  `DEP_NOM` varchar(255) NOT NULL,
  `DEP_VALUE` varchar(255) NOT NULL,
  `POSITION` int(11) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- ID : clé primaire.
-- ID_FORM : id du form dans lequel est le champ
-- NOM : nom du champ pour l'attr name du <input>
-- TITRE : titre affichédu champ
-- TYPE : type de champ (text, textarea, password, select, ...)
-- NOTE : note à l'attention de l'utilisateur (indice, précision)
-- REQUIRED : OUI ou NON si le champ est requis ou optionnel
-- ERROR : erreur affiché "ce champ est obligatoire" ou rien
-- VALUE : options séparés par des virgules pour les select, checkbox, radio... ou value par défaut.
-- DEP_NOM : champ auquel il est dépendant
-- DEP_VALUE : valeur du champ auquel il est dépendant
-- POSITION : position du champ dans le formulaire 

CREATE TABLE IF NOT EXISTS `form_result` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `ID_FORM` int(11) NOT NULL,
  `DATE_POST` datetime NOT NULL,
  `INC_PARAM` longtext NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

-- ID : clé primaire.
-- ID_FORM : id du form pour lequel est le result
-- INC_DATE : date du result
-- INC_PARAM : résultat sous la forme 
--				{#NOM_CHAMP#} value value value
--				value value value {!END#NOM_CHAMP#}
--				{##NOM_CHAMP2##} value {!END#NOM_CHAMP#}
--				{#NOM_CHAMP3#} value {!END#NOM_CHAMP#}
-- A parser en PHP pour affichage et analyse des résultats. 