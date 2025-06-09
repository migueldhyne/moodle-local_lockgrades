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

$string['backtoform'] = 'Retour au formulaire';
$string['error_invalididnumber'] = 'Le numéro d’identification de la catégorie spécifiée est introuvable.';
$string['error_noidnumber']    = 'Veuillez saisir un identifiant valide.';
$string['idnumber']            = 'Identifiant de la catégorie';
$string['lock_success']        = 'Les notes ont été verrouillées avec succès.';
$string['lockgrades']          = 'Verrouiller les évaluations';
$string['lockgrades:manage']   = 'Gérer le plugin lockgrades';
$string['lockgrades_info'] = '<strong>Note importante&nbsp;:</strong><br>
Lorsque vous verrouillez une note, Moodle peut afficher un message d&rsquo;avertissement dans le carnet de notes ainsi qu&rsquo;un bouton «&nbsp;Recalculer malgré tout&nbsp; ».<br>
<ul>
<li>Ce message signifie que toute modification des notes via l&rsquo;activité ne sera pas reportée tant que la note reste verrouillée.</li>
<li>Le bouton «&nbsp;Recalculer malgré tout&nbsp; » permet de forcer la mise à jour des notes, même pour les éléments verrouillés.</li>
<li>Utilisez ce bouton avec précaution&nbsp;: toute modification forcée peut écraser une note verrouillée et générer une incohérence.</li>
</ul>
Ce comportement est normal et vise à sécuriser la gestion des notes dans Moodle.';
$string['pluginname']          = 'Lock Grades';
$string['privacy:metadata']    = 'Le plugin local Lockgrades verrouille uniquement les notes (il n\'utilise aucune donnée).';
$string['unlock_success']   = 'Les notes ont été déverrouillées avec succès.';
$string['unlockgrades']        = 'Déverrouiller les évaluations';
