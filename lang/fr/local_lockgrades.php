<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Language strings for the local_lockgrades plugin.
 *
 * Contains all French language strings used by the Wiki Creator plugin,
 * including those for settings, interface labels, and messages.
 *
 * @package   local_lockgrades
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['actions'] = 'Actions';
$string['backtoform'] = 'Retour au formulaire';
$string['categoryid'] = 'Catégorie';
$string['courseid'] = 'Cours';
$string['delete'] = 'Delete';
$string['details'] = 'Détails';
$string['duplicate'] = 'Dupliquer';
$string['edit'] = 'Modifier';
$string['error_invalididnumber'] = 'Le numéro d’identification de la catégorie spécifiée est introuvable.';
$string['error_nofilteredcourses'] = 'Aucun élément trouvé dans les cours dont le shortname contient « {$a} ».';
$string['error_noidnumber'] = 'Veuillez saisir un identifiant valide.';
$string['executed'] = 'Exécutée';
$string['executiondate'] = 'Date d\'exécution';
$string['fullname'] = 'Nom complet du cours';
$string['history'] = 'Historique des opérations';
$string['idnumber'] = 'Identifiant de la catégorie';
$string['itemid'] = 'Élément';
$string['itemtype'] = 'Type';
$string['lock_success'] = 'Les notes ont été verrouillées avec succès.';
$string['lockgrades'] = 'Verrouiller les évaluations';
$string['lockgrades:manage'] = 'Gérer le plugin lockgrades';
$string['lockgrades_info'] = '<strong>Note importante :</strong><br>
Lorsque vous verrouillez une note, Moodle peut afficher un message d\'avertissement dans le carnet de notes ainsi qu&rsquo;un bouton «& Recalculer malgré tout ».<br>
<ul>
<li>Ce message signifie que toute modification des notes via l&rsquo;activité ne sera pas reportée tant que la note reste verrouillée.</li>
<li>Le bouton « Recalculer malgré tout » permet de forcer la mise à jour des notes, même pour les éléments verrouillés.</li>
<li>Utilisez ce bouton avec précaution : toute modification forcée peut écraser une note verrouillée et générer une incohérence.</li>
</ul>
Ce comportement est normal et vise à sécuriser la gestion des notes dans Moodle.';
$string['logdetails'] = 'Journal';
$string['pattern'] = 'Option : si le shortname contient...';
$string['pattern_help'] = 'Cette fonction est optionnelle : si la case est laissée vide, tous les cours seront traités sans distinction.';
$string['pluginname'] = 'Lock Grades';
$string['previewimpact'] = 'Prévisualiser l’impact';
$string['privacy:metadata'] = 'Le plugin local Lockgrades verrouille uniquement les notes (il n\'utilise aucune donnée).';
$string['schedule_success'] = 'L’action de verrouillage/déverrouillage a été planifiée avec succès.';
$string['scheduled'] = 'Planifiée';
$string['scheduledfor'] = 'Date programmée';
$string['scheduledtask_confirmdelete'] = 'Voulez-vous vraiment supprimer cette tâche programmée (exécution prévue le {$a}) ?';
$string['scheduledtask_deleted'] = 'La tâche programmée a bien été supprimée.';
$string['scheduledtask_duplicate'] = 'Dupliquer une tâche programmée';
$string['scheduledtask_duplicated'] = 'La tâche programmée a été dupliquée.';
$string['scheduledtask_edit'] = 'Modifier une tâche programmée';
$string['scheduledtask_updated'] = 'La tâche programmée a été mise à jour.';
$string['scheduledtasks'] = 'Tâches programmées';
$string['scheduledtime'] = 'Date et heure planifiées';
$string['scheduledtime_help'] = 'Sélectionnez la date et l’heure auxquelles exécuter le verrouillage/déverrouillage.';
$string['scheduledtype'] = 'Action';
$string['schedulelock'] = 'Planifier le verrouillage';
$string['schedulesubmit'] = 'Planifier';
$string['scheduleunlock'] = 'Planifier le déverrouillage';
$string['shortname'] = 'Nom abrégé du cours';
$string['status'] = 'Statut';
$string['taskname'] = 'Tâche planifiée de verrouillage/déverrouillage des notes';
$string['unlock_success'] = 'Les notes ont été déverrouillées avec succès.';
$string['unlockgrades'] = 'Déverrouiller les évaluations';
$string['warning_content'] = '<strong>À savoir :</strong> Moodle peut proposer un bouton « Recalculer malgré tout » dans le carnet de notes pour forcer une mise à jour des notes même verrouillées ; utilisez-le avec précaution.';
