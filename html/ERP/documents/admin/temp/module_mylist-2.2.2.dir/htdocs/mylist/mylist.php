<?php
/* Copyright (C) 2013-2016		Charlie Benke 		<charlie@patas-monkey.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	\file       	htdocs/mylist/mylist.php
 *	\ingroup    	mylist
 *	\brief      	list of selected fields
 */

$res=@include("../main.inc.php");                    // For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
    $res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../main.inc.php");        // For "custom" directory
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';


dol_include_once('/mylist/class/mylist.class.php');
dol_include_once("/mylist/core/modules/mylist/modules_mylist.php");

$socid=GETPOST('socid','int');
$rowid=GETPOST('rowid','int');
$action=GETPOST('action');

// load the mylist definition
$myliststatic = new Mylist($db);
$myliststatic->fetch($rowid);


if ($myliststatic->langs)
	foreach(explode(":", $myliststatic->langs) as $newlang)
		$langs->load($newlang);

$langs->load('mylist@mylist');
$langs->load('personalfields@mylist');

// Security check
$module='mylist';

if (! empty($user->societe_id))
	$socid=$user->societe_id;
	
if (! empty($socid))
{
	$objectid=$socid;
	$module='societe';
	$dbtable='&societe';
}

//$result = restrictedArea($user, $module, $objectid, $dbtable);

/*
 * Actions
 */
if (GETPOST('dojob')!="") 
{
	// on récupère les id à traiter
	$tbllistcheck= GETPOST('checksel');
	// on vérifie qu'il y a au moins une ligne de cochée
	if (count($tbllistcheck) >0)
	{
		foreach ($tbllistcheck as $rowidsel) 
		{
			// on récupère la requete à lancer
			$sqlQuerydo=$myliststatic->querydo;
			// on lance la requete
			$sqlQuerydo=str_replace("#ROWID#", $rowidsel, $sqlQuerydo);
			dol_syslog("mylist.php"."::sqlQuerydo=".$sqlQuerydo);
			//print $sqlQuerydo;
			$resultdo=$db->query($sqlQuerydo);
		}
	}
}

$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
if (! $sortfield) $sortfield='1';
if (! $sortorder) $sortorder='DESC';

$ArrayTable =$myliststatic->listsUsed;
$sql = "SELECT DISTINCT ". $myliststatic->GetSqlFields($ArrayTable);

// Replace the prefix tables
if ($dolibarr_main_db_prefix != 'llx_')
	$sql.= " ".preg_replace('/llx_/i',$dolibarr_main_db_prefix, $myliststatic->querylist);
else
	$sql.= " ".$myliststatic->querylist;

// init fields managment
if ($myliststatic->fieldinit)
{
	$tblInitFields=explode(":",$myliststatic->fieldinit);
	foreach ($tblInitFields as $initfields ) 
	{
		$tblInitField=explode("=",$initfields);
		$valueinit = GETPOST($tblInitField[0]);
		// on prend la valeur par défaut si la valeur n'est pas saisie...
		if (!$valueinit)
			$valueinit = $tblInitField[1];
		$sql=str_replace("#".$tblInitField[0]."#", $valueinit, $sql);
	}
}

// boucle sur les champs filtrables
$sqlfilter= $myliststatic->GetSqlFilterQuery($ArrayTable);

// pour gérer le cas du where dans la query
// si y a des champs à filter et pas de where dans la requete de base
if ($sqlfilter && strpos(strtoupper($sql), "WHERE") ==0)
	$sqlfilter= " WHERE 1=1 ".$sqlfilter;



// pour gérer le cas du filtrage selon utilisateur
if (strpos(strtoupper($sql), "#USER#") > 0)
	$sql=str_replace("#USER#", $user->id, $sql);


// pour gérer le cas du filtrage selon utilisateur
if (strpos(strtoupper($sql), "#USERGROUP#") > 0)
{
	$sqlg = "SELECT g.rowid, ug.entity as usergroup_entity";
	$sqlg.= " FROM ".MAIN_DB_PREFIX."usergroup as g,";
	$sqlg.= " ".MAIN_DB_PREFIX."usergroup_user as ug";
	$sqlg.= " WHERE ug.fk_usergroup = g.rowid";
	$sqlg.= " AND ug.fk_user = ".$user->id;
	if(! empty($conf->multicompany->enabled) && $conf->entity == 1 && $user->admin && ! $user->entity)
	{
		$sqlg.= " AND g.entity IS NOT NULL";
	}
	else
	{
		$sqlg.= " AND g.entity IN (0,".$conf->entity.")";
	}
	$sqlg.= " ORDER BY g.nom";
	$result = $db->query($sqlg);
	$ret=array();

	if ($result)
	{
		while ($obj = $db->fetch_object($result))
		{
			if (! array_key_exists($obj->rowid, $ret))
				$ret[$obj->rowid]=$newgroup;
		}
		$db->free($result);
	}
	$sql=str_replace("#USERGROUP#", explode(",",$ret), $sql);
}

// pour gérer le cas du filtrage selon l'entité
if (strpos(strtoupper($sql), "#ENTITY#") > 0)
	$sql=str_replace("#ENTITY#", $conf->entity, $sql);


// filtre sur l'id de l'élément en mode tabs
$idreftab=(GETPOST('id')?GETPOST('id'):GETPOST('socid'));
if (!empty($myliststatic->elementtab) && $idreftab != "")
{
	switch($myliststatic->elementtab) {
		case 'Societe' :
			// il faut la table societe as s
			//$sql.=", srowid as elementrowid";
			$sqlfilter.=" AND s.rowid=".$idreftab;
			break;
		case 'Product' :
		case 'Project' :
			// il faut la table product as p
			$sqlfilter.=" AND p.rowid=".$idreftab;
			break;
		case 'CategProduct' :
		case 'CategSociete' :
			// il faut la table categories as c
			$sqlfilter.=" AND c.rowid=".$idreftab;
			break;
	}

}

// on positionne les champs à filter avant un group by ou un order by
if (strpos(strtoupper($sql), 'GROUP BY') > 0)
{
	// on découpe le sql
	$sqlleft=substr($sql,0,strpos(strtoupper($sql), 'GROUP BY')-1);
	$sqlright=substr($sql,strpos(strtoupper($sql), 'GROUP BY'));
	$sql=$sqlleft." ".$sqlfilter." ".$sqlright;
}
elseif (strpos(strtoupper($sql), 'ORDER BY') > 0)
{
	// on découpe le sql
	$sqlleft=substr($sql,0,strpos(strtoupper($sql), 'ORDER BY')-1);
	$sqlright=substr($sql,strpos(strtoupper($sql), 'ORDER BY'));
	$sql=$sqlleft." ".$sqlfilter." ".$sqlright;
}
else
	$sql.= $sqlfilter;

// if we don't allready have a group by
if (strpos(strtoupper($sql), 'GROUP BY') == 0)
	$sql.= $myliststatic->GetGroupBy($ArrayTable);

// Si il y a un order by prédéfini dans la requete ou un rollup on désactive le tri
if (stripos($myliststatic->querylist, 'ORDER BY') + stripos($myliststatic->querylist, 'WITH ROLLUP') == 0) 
	$sql.= ' ORDER BY '.$sortfield.' '.$sortorder;


if (GETPOST('export')!="") 
{
	$sql = (GETPOST('sqlquery') ? GETPOST('sqlquery') : $sql);
	$sql = str_replace("#SEL#", "SELECT", $sql);
	$sep = ($conf->global->MYLIST_EXPORT_SEPARATOR ? $conf->global->MYLIST_EXPORT_SEPARATOR : ";");

	header('Content-Type: text/csv');
	header('Content-Disposition: attachment;filename=mylist_export'.$rowid.'.csv');
	$tmp="";
	foreach ($ArrayTable as $key => $fields) {
		if (! empty($fields['alias'])) 
			$tmp.=$fields['alias'];
		else
			// pour gérer les . des définitions de champs
			$tmp.=str_replace(array('.', '-'),'_',$fields['field']);
		$tmp.=$sep;
	}
	// on enlève la dernière virgule et l'espace en fin de ligne
	print substr($tmp,0,-1)."\n";


	dol_syslog("mylist.php"."::export sql=".$sql);
	$result=$db->query($sql);
//print $sql;
	if ($result)
	{
		$num = $db->num_rows($resql);

		$i = 0;
		// on boucle sur les lignes de résultats
		while ($i < $num)
		{
			$objp = $db->fetch_object($result);
			$tmp="";
			//var_dump($objp);
			foreach ($ArrayTable as $key => $fields) 
			{
				
				if ($fields['alias']!="")
					$fieldsname=$fields['alias'];
				else
					$fieldsname=str_replace(array('.', '-'),"_",$fields['field']);

				// pour virer les url des champs de type lien
				if ((strpos($fields['field'], '.rowid') > 0 || strpos($fields['field'], '.id') > 0)  && $fields['param'])
				{
					// pour les clés qui sont lié à un autre élément
					$tblelement=explode(":",$fields['param']);
					if ($tblelement[1]!="")
						dol_include_once ($tblelement[1]);

					// seulement si le champs est renseigné
					if ($objp->$fieldsname)
					{
						$objectstatic = new $tblelement[0]($db);
						$objectstatic->id=$objp->$fieldsname;
						$objectstatic->fetch($objp->$fieldsname);
						$url=$objectstatic->getNomUrl(0);
						$tmp.=html_entity_decode (substr( $url, strpos($url,">")+1,-4),   ENT_COMPAT | ENT_HTML401, "ISO-8859-1");
					}
					$tmp.=$sep;
				}
				elseif (strpos($fields['field'], 'fk_') > 0 && $fields['param']) 
				{
					$tblelement=explode(":",$fields['param']);
					if ($tblelement[1]!="")
						dol_include_once ($tblelement[1]);
					// cas à part des status
					if (strpos($fields['field'], 'fk_statut') > 0 )
					{
						$objectstatic = new $tblelement[0]($db);
						$objectstatic->statut=$objp->$fieldsname;
						// for compatibility case
						$objectstatic->fk_statut=$objp->$fieldsname;
						if ($objp->f_paye == 1)
							$objectstatic->paye=1;
						$tmp.=html_entity_decode ($objectstatic->getLibStatut(1),   ENT_COMPAT | ENT_HTML401, "ISO-8859-1");
					}
					else
					{
						if ($objp->$fieldsname)
						{
							$objectstatic = new $tblelement[0]($db);
							$objectstatic->id=$objp->$fieldsname;
							$objectstatic->fetch($objp->$fieldsname);
							$tmp.=$objectstatic->ref;
						}
						else
							$tmp.=html_entity_decode ($myliststatic->get_infolist($objp->$fieldsname,$fields['param']), ENT_COMPAT | ENT_HTML401, "ISO-8859-1");
					}
					$tmp.=$sep;
				}
				else
				{
					// selon le type de données
					switch($fields['type'])
					{
						case "Price":
						case "Number":
							$tmp.=price($objp->$fieldsname);
							break;
							
						case "Percent":
							$tmp.=price($objp->$fieldsname * 100 )." %";
							break;
			
						case "Date":
							$tmp.=dol_print_date($db->jdate($objp->$fieldsname),'day');
							break;
			
						case "Boolean":
							$tmp.=yn($objp->$fieldsname);
							break;
			
						default:
							$value=$objp->$fieldsname;
							if ($conf->global->MYLIST_CRLF_REPLACE)
								$value=str_replace("\n", $conf->global->MYLIST_CRLF_REPLACE, $value );
							$tmp.='"';
							$tmp.=html_entity_decode ($value, ENT_COMPAT | ENT_HTML401, "ISO-8859-1");
							$tmp.='"';
							break;
					}
					$tmp.=$sep;
				}
			}
			print substr($tmp,0,-1)."\n";
			$i++;
		}
	}
	$db->close();
	exit;
}

if ($action== 'builddoc') 
{
	/*
	 * Generate mylist document
	 * define into /core/modules/mylist/modules_mylist.php
	 */
	$ret = $myliststatic->fetch($rowid); // Reload to get new records
	// on conserve la requete sql pour l'édition
	$myliststatic->sqlquery=$sql;
	
	
	// Save last template used to generate document
	$myliststatic->id= $rowid;
	if (GETPOST('model')) $myliststatic->setDocModel($user, GETPOST('model','alpha'));

	// Define output language
	$outputlangs = $langs;
	if (! empty($conf->global->MAIN_MULTILANGS)) {
		$outputlangs = new Translate("", $conf);
		$newlang = (GETPOST('lang_id') ? GETPOST('lang_id') : $object->thirdparty->default_lang);
		$outputlangs->setDefaultLang($newlang);
	}

//var_dump($myliststatic);

	$result=mylist_create($db, $myliststatic, GETPOST('model','alpha'), $outputlangs);
	
	if ($result <= 0)
	{
		setEventMessages($object->error, $object->errors, 'errors');
		$action='';
	}
}


// Remove file in doc form
else if ($action == 'remove_file' ) {
	if ($myliststatic->rowid > 0) {
		require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

		$langs->load("other");
		$upload_dir = $conf->mylist->dir_output;
		$file = $upload_dir . '/' . GETPOST('file');
		$ret = dol_delete_file($file, 0, 0, 0, $object);
		if ($ret) setEventMessage($langs->trans("FileWasRemoved", GETPOST('file')));
		else setEventMessage($langs->trans("ErrorFailToDeleteFile", GETPOST('file')), 'errors');
	}
}

/*
 * View
 */

// mode onglet : il est actif et une clé est transmise
$idreftab=(GETPOST('id')?GETPOST('id'):GETPOST('socid'));
if (!empty($myliststatic->elementtab) && $idreftab != "")
{
	$form = new Form($db);
	// si dolversion >= 5 loader  datatables
	llxHeader();
	switch($myliststatic->elementtab) {
		case 'Societe' :
			require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
			$objecttab = new Societe($db);
			$result = $objecttab->fetch($idreftab);
			$head = societe_prepare_head($objecttab);
			dol_fiche_head($head, 'mylist_'.$myliststatic->rowid, $langs->trans("ThirdParty"), 0, 'company');

			print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<table class="border" width="100%">';
			print '<tr><td width="20%">'.$langs->trans('ThirdPartyName').'</td>';
			print '<td colspan="3">';
			print $form->showrefnav($objecttab,'id','',($user->societe_id?0:1),'rowid','nom','','&code='.$codeListable);
			print '</td></tr>';

			if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
				print '<tr><td>'.$langs->trans('Prefix').'</td><td colspan="3">'.$objecttab->prefix_comm.'</td></tr>';


			if ($objecttab->client)
			{
				print '<tr><td>';
				print $langs->trans('CustomerCode').'</td><td colspan="3">';
				print $objecttab->code_client;
				if ($objecttab->check_codeclient() <> 0) print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
				print '</td></tr>';
			}

			if ($objecttab->fournisseur)
			{
				print '<tr><td>';
				print $langs->trans('SupplierCode').'</td><td colspan="3">';
				print $objecttab->code_fournisseur;
				if ($objecttab->check_codefournisseur() <> 0) print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
				print '</td></tr>';
			}
			print '</table></form><br>';

			break;

		case 'Product' :
			require_once(DOL_DOCUMENT_ROOT."/core/lib/product.lib.php");
			require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
			$objecttab = new Product($db);
			$result = $objecttab->fetch($idreftab);
			$head = product_prepare_head($objecttab, $user);
			dol_fiche_head($head, 'mylist_'.$myliststatic->rowid, $langs->trans("Product"), 0, 'product');
			
			print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<table class="border" width="100%">';

			print '<tr>';
			print '<td width="30%">'.$langs->trans("Ref").'</td><td colspan="3">';
			print $form->showrefnav($objecttab,'ref','',1,'ref');
			print '</td>';
			print '</tr>';

			// Label
			print '<tr><td>'.$langs->trans("Label").'</td><td colspan="3">'.$objecttab->libelle.'</td></tr>';

			// Status (to sell)
			print '<tr><td>'.$langs->trans("Status").' ('.$langs->trans("Sell").')</td><td>';
			print $objecttab->getLibStatut(2,0);
			print '</td></tr>';

			// Status (to buy)
			print '<tr><td>'.$langs->trans("Status").' ('.$langs->trans("Buy").')</td><td>';
			print $objecttab->getLibStatut(2,1);
			print '</td></tr>';

			print '</table></form><br>';

			break;

		case 'project' :
		
			require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
			require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
			$objecttab = new Project($db);
			$result = $objecttab->fetch($idreftab);
			
			$head = project_prepare_head($objecttab);
			dol_fiche_head($head, "customtabs_".$tabsid, $langs->trans('Project'), 0, ($objecttab->public?'projectpub':'project'));
			
			print '<table class="border" width="100%">';
			
			$linkback = '<a href="'.DOL_URL_ROOT.'/projet/liste.php">'.$langs->trans("BackToList").'</a>';
			
			// Ref
			print '<tr><td width="30%">'.$langs->trans("Ref").'</td><td>';
			// Define a complementary filter for search of next/prev ref.
			if (! $user->rights->projet->all->lire)
			{
				$mine = $_REQUEST['mode']=='mine' ? 1 : 0;
			    $projectsListId = $objecttab->getProjectsAuthorizedForUser($user,$mine,0);
			    $objecttab->next_prev_filter=" rowid in (".(count($projectsListId)?join(',',array_keys($projectsListId)):'0').")";
			}
			print $form->showrefnav($objecttab, 'ref', $linkback, 1, 'ref', 'ref').'</td></tr>';
			
			// Label
			print '<tr><td>'.$langs->trans("Label").'</td><td>'.$objecttab->title.'</td></tr>';
			
			print '<tr><td>'.$langs->trans("Company").'</td><td>';
			if ($objecttab->socid > 0)
			{
				$objsoc = new Societe($this->db);
				$objsoc->fetch($objecttab->socid);
				print $objsoc->getNomUrl(1);
			}
			else print'&nbsp;';
			print '</td></tr>';
			
			// Visibility
			print '<tr><td>'.$langs->trans("Visibility").'</td><td>';
			if ($objecttab->public) 
				print $langs->trans('SharedProject');
			else 
				print $langs->trans('PrivateProject');
			print '</td></tr>';
			
			// Statut
			print '<tr><td>'.$langs->trans("Status").'</td><td>'.$objecttab->getLibStatut(4).'</td></tr>';
			break;


		case 'CategSociete' :
			require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
			require_once DOL_DOCUMENT_ROOT.'/core/lib/categories.lib.php';

			$objecttab = new Categorie($db);
			$result = $objecttab->fetch($idreftab);

			$title=$langs->trans("SocietesCategoryShort");
			$type = 2;
			$head = categories_prepare_head($objecttab, $type);
			dol_fiche_head($head, 'mylist_'.$myliststatic->rowid, $title, 0, 'category');

			print '<table class="border" width="100%">';

			// Path of category
			print '<tr><td width="20%" class="notopnoleft">';
			$ways = $objecttab->print_all_ways();
			print $langs->trans("Ref").'</td><td>';
			print '<a href="'.DOL_URL_ROOT.'/categories/index.php?leftmenu=cat&type='.$type.'">'.$langs->trans("Root").'</a> >> ';
			foreach ($ways as $way)
			{
				print $way."<br>\n";
			}
			print '</td></tr>';

			// Description
			print '<tr><td width="20%" class="notopnoleft">';
			print $langs->trans("Description").'</td><td>';
			print nl2br($objecttab->description);
			print '</td></tr>';		
			print '</table><br>';
			break;

		case 'CategProduct' :
			require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
			require_once DOL_DOCUMENT_ROOT.'/core/lib/categories.lib.php';

			$objecttab = new Categorie($db);
			$result = $objecttab->fetch($idreftab);

			$title=$langs->trans("ProductsCategoryShort");
			$type = 0;
			$head = categories_prepare_head($objecttab, $type);
			dol_fiche_head($head, 'mylist_'.$myliststatic->rowid, $title, 0, 'category');

			print '<table class="border" width="100%">';

			// Path of category
			print '<tr><td width="20%" class="notopnoleft">';
			$ways = $objecttab->print_all_ways();
			print $langs->trans("Ref").'</td><td>';
			print '<a href="'.DOL_URL_ROOT.'/categories/index.php?leftmenu=cat&type='.$type.'">'.$langs->trans("Root").'</a> >> ';
			foreach ($ways as $way)
			{
				print $way."<br>\n";
			}
			print '</td></tr>';

			// Description
			print '<tr><td width="20%" class="notopnoleft">';
			print $langs->trans("Description").'</td><td>';
			print nl2br($objecttab->description);
			print '</td></tr>';		
			print '</table><br>';
			break;
	}
}
else
	llxHeader('',$myliststatic->label,'EN:mylist_EN|FR:mylist_FR|ES:mylist_ES');

$form = new Form($db);
$formother = new FormOther($db);
$formfile = new FormFile($db);


$now=dol_now();


if (empty($conf->global->MAIN_USE_JQUERY_DATATABLES)) 
{

	$page = GETPOST("page",'int');
	if ($page == -1) { $page = 0; }
	$offset = $conf->global->MYLIST_NB_ROWS * $page;
	$pageprev = $page - 1;
	$pagenext = $page + 1;
	
}

// construction de la requete sql 
// gestion de la limite des lignes si on ne force pas à tous voir
if ($myliststatic->forceall != 1)
{
	$limit = $conf->global->MYLIST_NB_ROWS;
	if ( ! $limit )	$limit = 25;
	$sql.= $db->plimit($limit + 1,$offset);
}
else
	if ( empty($conf->global->MAIN_USE_JQUERY_DATATABLES)) $sql.= $db->plimit($limit + 1,$offset);


//  pour les tests on affiche la requete SQL 
if ($myliststatic->active ==0)  // lancement de la requete à partir du menu mylist
	print $sql;
	

dol_syslog("mylist.php"."::sql=".$sql);
$result=$db->query($sql);


if ($result)
{
    $num = $db->num_rows($resql);
    $i = 0;
	
	// génération dynamique du param

	$param.="&rowid=".$rowid;
	
	if ( empty($conf->global->MAIN_USE_JQUERY_DATATABLES))
	{
		// ajout des filtres 
		$param.=$myliststatic->GenParamFilterFields($ArrayTable);
		$param.=$myliststatic->GenParamFilterInitFields();
		print_barre_liste($myliststatic->label  , $page, $_SERVER["PHP_SELF"],$param, $sortfield, $sortorder, '', $num);
	}
	else
		print_barre_liste($myliststatic->label  , $page, $_SERVER["PHP_SELF"],$param, $sortfield, $sortorder, '', 0);

	// Lignes des champs de filtre
	print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="rowid" value="'.$rowid.'">';

	// champs filtrés, champ personnalisés et case à cocher
	if (! empty($conf->global->MAIN_USE_JQUERY_DATATABLES))
	{
		print '<div STYLE="float:left;">';
		print '<input type="image" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png"  value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'">';
		print '</div>';
		// gestion des champs personnalisés
		if (! empty($myliststatic->fieldinit))
		{
			print '<div STYLE="float:left;">';
			print $myliststatic->GenFilterInitFieldsTables();
			print '</div><br><br><br>';
		}
		// boucle sur les champs filtrables
		print $myliststatic->GenFilterFieldsTables($ArrayTable);


		// gestion de la requete de mise à jour en masse
		if (! empty($myliststatic->querydo))
		{	// on récupère le champ servant de clé pour la ligne
			foreach ($ArrayTable as $key => $fields) 
			{
				if ($fields['type'] == 'Check')
					if ($fields['alias']!="")
						$lineid=$fields['alias'];
					else
						$lineid=str_replace(array('.', '-'),"_",$fields['field']);
					//print "===".$lineid."<br>";
			}
		}

		print '<br><br>';
		print '<table id="listtable" class="noborder" width="100%">';
		print "<thead>\n";
		print '<tr class="liste_titre">';
		foreach ($ArrayTable as $key => $fields) 
			print "<th align=left>".$langs->trans($fields['name'])."</th>";
		if (! empty($myliststatic->querydo))  print "<th>Sel.</th>";
		print '</tr>';
		print "</thead>\n";
	}
	else
	{
		print '<table class="liste" width="100%">';

		if (! empty($myliststatic->fieldinit))
		{
			print '<tr class="liste_titre">';
			print $myliststatic->GenFilterInitFieldsTables();
			print '</tr>';
		}

		print '<tr class="liste_titre">';
		// si il y a une requete de mise à jour
		
		foreach ($ArrayTable as $key => $fields)
			if ($fields['visible']=='1')
				print_liste_field_titre($langs->trans($fields['name']),$_SERVER["PHP_SELF"],$key,'',$param, 'align="'.$fields['align'].'"', $sortfield,$sortorder);
		if (! empty($myliststatic->querydo))  print "<th></th>";
		print "<th></th></tr>\n";

		print '<tr class="liste_titre">';
		
		print $myliststatic->GenFilterFieldsTables($ArrayTable);
		print '<td><input type="image" name="button_search" src="'.DOL_URL_ROOT.'/theme/'.$conf->theme.'/img/search.png"  value="'.dol_escape_htmltag($langs->trans("Search")).'" title="'.dol_escape_htmltag($langs->trans("Search")).'"></td>';
		if (! empty($myliststatic->querydo))  print "<th></th>";
		print "</tr>\n";
	}
	print "<tbody>\n";
	
	$var=true;
	$total=0;
	$subtotal=0;

	if (! empty($conf->global->MAIN_USE_JQUERY_DATATABLES))
	{
		// en mode datatable si un filtre est appliqué 
		if ($sqlfilter !="" || $myliststatic->forceall == 1)
			$limit=$num;				// on affiche tous les enregistrements
		else
			$limit=min($num,$limit * 4);	// sinon on affiche soit le nombre, soit (4 pages par défaut )
	}
	else
	{
		// en mode standard on affiche la limite au max
		$limit=min($num,$limit);
	}

	while ($i < $limit)
	{
		$objp = $db->fetch_object($result);

		$now = dol_now();
		$var=!$var;
		print '<tr '.$bc[$var].'>';
		foreach ($ArrayTable as $key => $fields) 
		{
			if (!empty($conf->global->MAIN_USE_JQUERY_DATATABLES) || $fields['visible']=='1')
			{
				if ($fields['alias']!="")
					$fieldsname=$fields['alias'];
				else
					$fieldsname=str_replace(array('.', '-'),"_",$fields['field']);
				$tblelement=explode(":",$fields['param']);

				switch($fields ['type'])
				{
					case 'Statut':
						// pour les champs de type statut
						print '<td nowrap="nowrap" align="'.$fields['align'].'">';
						$objectstatic = new $tblelement[0]($db);
						$rowidfields=str_replace('fk_statut','rowid',$fields['field']);
						$rowidfieldsname=str_replace(array('.', '-'),"_",$rowidfields);
						if ($objp->$rowidfieldsname)
							$objectstatic->fetch($objp->$rowidfieldsname);
						$objectstatic->statut=$objp->$fieldsname;
						// for compatibility case
						$objectstatic->fk_statut=$objp->$fieldsname;
						print $objectstatic->getLibStatut(5);
						print '</td>';
						break;
					case 'List':
					case 'Text':
						if ($fields['param'] == "")
						{
							print $myliststatic->genDefaultTD ($fields['field'], $fields, $objp);
							break;
						}
						// pour les clés qui sont lié à un autre élément
						print '<td nowrap="nowrap" align="'.$fields['align'].'">';

						switch(count($tblelement))
						{
							// valeur issue d'une table
							case 3:
								$sqlelem = 'SELECT '.$tblelement[1].' as rowid, '.$tblelement[2].' as label';
								$sqlelem.= ' FROM '.MAIN_DB_PREFIX .$tblelement[0];
								$sqlelem.= ' WHERE '.$tblelement[1].'='.$objp->$fieldsname;
								$resqlf = $db->query($sqlelem);

								if ($resqlf)
								{
									$objf = $db->fetch_object($resqlf);
									print $objf->label;
								}
								break;

							// valeur lié à un élément
							default :	
								if ($tblelement[1]!="")
									dol_include_once ($tblelement[1]);
								// seulement si le champs est renseigné
								if ($objp->$fieldsname)
								{
									$objectstatic = new $tblelement[0]($db);
									if ($fields ['type'] == 'List')
										$objectstatic->fetch($objp->$fieldsname);
									else
										$objectstatic->fetch(0,$objp->$fieldsname);
									if (method_exists ($objectstatic,'getNomUrl'))
										print $objectstatic->getNomUrl(1);
									else
										print $objectstatic->$tblelement[3];
									
								}
							break;
						}
						print '</td>';
						break;
					case 'TooltipList' :
						if ($conf->global->MAIN_MODULE_MYLISTMORE == 1)
						{
							dol_include_once('/mylistmore/core/lib/tooltiplist.lib.php');
							print gettooltiplist($fields['param'],$objp->$fieldsname);
						}
						break;
					case 'ExtrafieldList' :
						if ($conf->global->MAIN_MODULE_MYLISTMORE == 1)
						{
							dol_include_once('/mylistmore/core/lib/extrafieldlist.lib.php');
							print getextrafieldlist($fields['param'], $objp->$fieldsname);
						}
						break;

					default :
						// affichage par défaut
						print $myliststatic->genDefaultTD ($fields['field'], $fields, $objp);
						break;
				}
			}
		}
		// si il y a une requete de mise à jour
		if (! empty($myliststatic->querydo))
		{
			print "\n";
			print '<td align=right>';
			print '<input type="checkbox" name="checksel[]" value="'.$objp->$lineid.'">';
			print '</td>'; 
		}
		if (! empty($conf->global->MAIN_USE_JQUERY_DATATABLES))
			print "</tr>\n";
		else
			print "<td></td></tr>\n";
		$i++;
	}
	print '</tbody>';
	print '</table>';


	print '<br><br><table width=100%><tr>';
	print '<td width=50% >';

	if ($conf->global->MYLIST_ADDON_PDF && $myliststatic->model_pdf != -1 )
	{
		$comref = dol_sanitizeFileName($myliststatic->label);
		$filedir = $conf->mylist->dir_output . '/' . $comref;
		$urlsource=$_SERVER["PHP_SELF"]."?rowid=".$myliststatic->rowid;
		$somethingshown=$formfile->show_documents('mylist',$comref,$filedir,$urlsource,1,1,$myliststatic->model_pdf,1,0,0,28,0,'','','',$soc->default_lang);
	}
	print '</td>';	
	print '<td align=left width=25% >';	

	$sqlQuery=str_replace("SELECT", "#SEL#", $sql);
	print '<input type=hidden name=sqlquery value="'.$sqlQuery.'">';
	if ($conf->global->MYLIST_CSV_EXPORT =="1" && $myliststatic->export == 1)
		print "<input class='butAction' type=submit name='export' value='".$langs->trans("ExportCSV")."'>";
		//print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?rowid='.$rowid.'&action=export">'.$langs->trans('ExportCSV').'</a>';
		//print '<input class="butAction" type=submit name="printpdf" value="'.$langs->trans('PrintResult').'" >';
	
	if (! empty($myliststatic->querydo))
		print '<input class="butAction" type=submit name="dojob" value="'.$langs->trans('DoJob').'" >';

	print '</td>';	
	print '<td align=left width=25% >';	
	$hookmanager->initHooks(array('mylist'));
	$parameters=array('id'=>$myliststatic->rowid, 'sql'=>$sql);
	$reshook=$hookmanager->executeHooks('MylistOptions',$parameters, $myliststatic, $action);    // Note that $action and $object may have been modified by some hooks
	print '</td>';
	print '</tr></table>';	
print '</form>';
}
else
{
	dol_print_error($db);
}

// si datatable est actif on cache les champs affichables
if (!empty($conf->global->MAIN_USE_JQUERY_DATATABLES))
{
	print "\n";
	print '<script type="text/javascript">'."\n";
	print 'jQuery(document).ready(function() {'."\n";
	print 'jQuery("#listtable").dataTable( {'."\n";
	//print '"sDom": \'TCR<"clear">lfrtip\','."\n";
	print '"sDom": \'Biltpr\','."\n";
	//print '"oColVis": {"buttonText": "'.$langs->trans('showhidecols').'" },'."\n";
	print '"buttons" : [ "colvis" ],';
	print '"language": { buttons: { "colvis": \''.$langs->trans('showhidecols').'\'} },';
	print '"bPaginate": true,'."\n";
	print '"bFilter": false,'."\n";
	// need on new datables version
	print '"colReorder": true,'."\n";
	print '"sPaginationType": "full_numbers",'."\n";
	print $myliststatic->gen_aoColumns($ArrayTable, !empty($myliststatic->querydo)); // pour gérer le format de certaine colonnes
	print $myliststatic->gen_aasorting($sortfield, $sortorder, $ArrayTable, !empty($myliststatic->querydo)); // pour gérer le trie par défaut dans la requete SQL
	print '"bJQueryUI": false,'."\n"; 
	print '"oLanguage": {"sUrl": "'.$langs->trans('datatabledict').'" },'."\n";
	print '"iDisplayLength": '.$conf->global->MYLIST_NB_ROWS.','."\n";
	print '"aLengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],'."\n";
	print '"bSort": true'."\n";
	//print '"oTableTools": { "sSwfPath": "../includes/jquery/plugins/datatables/extras/TableTools/swf/copy_csv_xls_pdf.swf" }'."\n";
	print '} );'."\n";
	print '});'."\n";

	// extension pour le trie
	print 'jQuery.extend( jQuery.fn.dataTableExt.oSort, {';
	// pour gérer les . et les , des décimales et le blanc des milliers
	print '"numeric-comma-pre": function ( a ) {';
	print 'var x = (a == "-") ? 0 : a.replace( /,/, "." );';
	print 'x = x.replace( " ", "" );';
	print 'return parseFloat( x );';
	print '},';
	print '"numeric-comma-asc": function ( a, b ) {return ((a < b) ? -1 : ((a > b) ? 1 : 0));},';
	print '"numeric-comma-desc": function ( a, b ) {return ((a < b) ? 1 : ((a > b) ? -1 : 0));},';
	
	// pour gérer les dates au format européenne
	print '"date-euro-pre": function ( a ) {';
    print 'if ($.trim(a) != "") {';
    print 'var frDatea = $.trim(a).split("/");';
    print 'var x = (frDatea[2] + frDatea[1] + frDatea[0]) * 1;';
    print '} else { var x = 10000000000000; }';
	print 'return x;';
    print '},';
 	print '"date-euro-asc": function ( a, b ) {return a - b; },';
 	print '"date-euro-desc": function ( a, b ) {return b - a;}';
	print '} );';
	print "\n";
	print '</script>'."\n";

	print $myliststatic->genHideFields($ArrayTable);
}

// End of page
llxFooter();
$db->close();
?>