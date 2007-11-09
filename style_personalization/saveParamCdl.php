<?
#########################################################################
# BEGIN COPYRIGHT, LICENSE AND WARRANTY NOTICE
# SOFTWARE NAME: Confort de lecture
# SOFTWARE RELEASE: 2.0.0
# COPYRIGHT NOTICE: Copyright (C) 2000-2007 GIE Confort de lecture (SQLI & HandicapZ�ro)
# SOFTWARE LICENSE: GNU General Public License v3
# NOTICE:
# This file is part of Confort de lecture.
#
# Confort de lecture is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
#
# Confort de lecture is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along with Confort de lecture.  If not, see <http://www.gnu.org/licenses/>.
#########################################################################

	require("inc/constantes_fonctions.php");
	require("inc/session.php");

# ----------------------------------------------------------------------
# V�rifie si on est sur le site en ligne ou le site en d�veloppement
# ----------------------------------------------------------------------

// Recup�ration des diff�rents param�tres
$idSite			= $_REQUEST['id'];
$pageOriginale	= $_REQUEST['p'];
$coulEcran		= $_REQUEST['cdlB_'];
$coulTexte		= $_REQUEST['cdlF_'];
$tailleTexte	= $_REQUEST['cdlS_'];
$userCdl		= $_SESSION["loginUserCDL"];
$idUserCdl		= $_SESSION["userCDL"];
$pseudoCdl		= $_REQUEST['pseudoModif'];
$mdpCdl			= $_REQUEST['mdpModif'];
$confMdpCdl		= $_REQUEST['confMdpModif'];

$_cdlJs = $_REQUEST['cdlJs'] ? $_REQUEST['cdlJs'] : 0;
$_cdlFrames = $_REQUEST['cdlFrames'] ? $_REQUEST['cdlFrames'] : 0;
$_cdlImg = $_REQUEST['cdlImg'] ? $_REQUEST['cdlImg'] : 0;
$_cdlObj = $_REQUEST['cdlObj'] ? $_REQUEST['cdlObj'] : 0;
$_cdlApplet = $_REQUEST['cdlApplet'] ? $_REQUEST['cdlApplet'] : 0;
$_cdlTables = $_REQUEST['cdlTables'] ? $_REQUEST['cdlTables'] : 0;


switch($tailleTexte) {
	case "100" : $tailleTexte = "1"; break;
	case "125" : $tailleTexte = "2"; break;
	case "220" : $tailleTexte = "4"; break;
	case "300" : $tailleTexte = "5"; break;
	default: $tailleTexte = "3"; break;
}

// V�rification des param�tres
$codeErrId = "";
$codeErrMdp = "";
$codeErrCMdp = "";
$codeErreur = "";
if ($pseudoCdl=="" || $pseudoCdl=="Identifiant") {
	$codeErrId = "1";
}
if ($mdpCdl=="") {
	$codeErrMdp = "1";
}
if ($confMdpCdl=="") {
	$codeErrCMdp = "1";
}
if ($mdpCdl!=$confMdpCdl) {
	$codeErreur = "4";
}
if ($codeErreur!="" || $codeErrId!="" || $codeErrCMdp!="" || $codeErrMdp!="") {
	header("Location:parametrage.php?id=".$idSite."&p=".$pageOriginale."&b=".$coulEcran."&f=".$coulTexte."&s=".$tailleTexte."&err=1&pseudoCdl=".$pseudoCdl."&style=".$_REQUEST['style']."#erreur");
	exit(0);
}

$sqlQuery = "SELECT ID_USER FROM users WHERE LOGIN_USER = ".quote_smart($pseudoCdl);
$res = $DB->execute($sqlQuery);
if($DB->sqlErrNo()) {
	echo $DB->sqlError();exit();
}

// L'utilisateur est-il d�j� logu� ? , modif simple
if ($userCdl and ($userCdl == $pseudoCdl)) {
	// On modifie ses param�tres
	$sqlQuery = "UPDATE users set FONT_COLOR=".quote_smart($coulTexte).", BACKGROUND_COLOR=".quote_smart($coulEcran).", FONT_SIZE=".quote_smart($tailleTexte).", ACTIVATE_JS=".$_cdlJs.", ACTIVATE_FRAMES=".$_cdlFrames.", DISPLAY_IMAGES=".$_cdlImg.", DISPLAY_OBJECTS=".$_cdlObj.", DISPLAY_APPLETS=".$_cdlApplet.", PARSE_TABLES=".$_cdlTables.", LOGIN_USER=".quote_smart($pseudoCdl).", PASSWORD_USER=".quote_smart(md5($mdpCdl)).", UPDATE_TIME = NOW() WHERE ID_USER=" . $idUserCdl;
	$res = $DB->execute($sqlQuery);
	if($DB->sqlErrNo()) {
		echo $DB->sqlError();exit();
	}
} else {
	// Utilisateur non encore logu�
	$sqlQuery = "SELECT ID_USER FROM users WHERE LOGIN_USER = ".quote_smart($pseudoCdl);
	$res = $DB->execute($sqlQuery);
	if($DB->sqlErrNo()) {
		echo $DB->sqlError();exit();
	}

	// L'utilisateur existe ou pas ?
	if($row = $DB->fetchArray($res)) {
		// L'utilisateur existe, erreur, on renvoir vers la page de param�trage
		header("Location:parametrage.php?id=".$idSite."&p=".$pageOriginale."&b=".$coulEcran."&f=".$coulTexte."&s=".$tailleTexte."&err=2&pseudoCdl=".$pseudoCdl."&style=".$_REQUEST['style']."#erreur");
		exit(0);
	} else {
		// L'utilisateur n'existe pas, on l'insere dans la base
		$sqlQuery = "INSERT INTO users (FONT_SIZE, FONT_COLOR, BACKGROUND_COLOR, ACTIVATE_JS, ACTIVATE_FRAMES, DISPLAY_IMAGES, DISPLAY_OBJECTS, DISPLAY_APPLETS, PARSE_TABLES, LOGIN_USER, PASSWORD_USER, CREATE_TIME, UPDATE_TIME) ";
		$sqlQuery .= "VALUES (".quote_smart($tailleTexte).",".quote_smart($coulTexte).",".quote_smart($coulEcran).",".$_cdlJs.",".$_cdlFrames.",".$_cdlImg.",".$_cdlObj.",".$_cdlApplet.",".$_cdlTables.",".quote_smart($pseudoCdl).",".quote_smart(md5($mdpCdl)).", NOW(),NOW())";
		$DB->execute($sqlQuery);
		if($DB->sqlErrNo()) {
			echo $DB->sqlError();exit();
		}
		$_SESSION["loginUserCDL"] = $pseudoCdl;

		$sqlQuery = "SELECT ID_USER FROM users WHERE LOGIN_USER = ".quote_smart($pseudoCdl)." AND PASSWORD_USER = ".quote_smart(md5($mdpCdl));
		$res = $DB->execute($sqlQuery);
		if($DB->sqlErrNo()) {
			echo $DB->sqlError();exit();
		}
		if($row = $DB->fetchArray($res)) {
			$_SESSION['userCDL']	= $row['ID_USER'];
		}
	}
}
$DB->freeResult($res);

$urlParser = "$parserUrl?cdlid=$idSite&cdlurl=".urlencode(preg_replace("{^/}", "", $pageOriginale))."&cdlfirst=1&cdlfs=$tailleTexte&cdlfc=$coulTexte&cdlbc=$coulEcran&cdljs=".$_cdlJs."&cdlframes=".$_cdlFrames."&cdlimg=".$_cdlImg."&cdlobject=".$_cdlObj."&cdlapplet=".$_cdlApplet."&cdtable=".$_cdlTables."&cdlstyle=".$_REQUEST['style'];

// Redirection vers la page de param�trage
header("Location: $urlParser");

?>