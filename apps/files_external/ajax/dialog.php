<?php
/**
 * ownCloud
 *
 * @author Juan Pablo Villafañez Ramos <jvillafanez@owncloud.com>
 * @author Jesus Macias Portela <jesus@owncloud.com>
 * @copyright (C) 2014 ownCloud, Inc.
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

OCP\JSON::checkAppEnabled('files_external');
OCP\JSON::checkLoggedIn();
OCP\JSON::callCheck();

$l = OC_L10N::get('files_external');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $target = $_GET['target'];
    if (strpos($target, '..') !== false) {
        exit();
    }

    /* Check mandatory params */
    $id_username = OC_Util::sanitizeHTML($_GET['iduser']);
    $id_password = OC_Util::sanitizeHTML($_GET['idpass']);
    $id_mount = OC_Util::sanitizeHTML($_GET['idmount']);
    $actionUrl = OCP\Util::linkTo('files_external', 'ajax/' . $target);
    $mountPoint = $_GET["m"];
    $folder = $_GET['name'];
    $smburl = $_GET['url'];
    $smbshare = $_GET['share'];

    // we'll need a bit of special processing here: replace "\n\n" into "<br/>" and surround
    // the folder name with html tags
    $enterCredentialsText = $l->t('Please enter correct credentials to mount %s folder', '%s');
    $sanitizedFolder = OC_Util::sanitizeHTML($folder);
    $translatedText = OC_Util::sanitizeHTML($enterCredentialsText);
    $spanText = str_replace('%s', "<strong>$sanitizedFolder</strong>", $translatedText);

    $tmpl = new OCP\Template('files_external', 'credentialsDialog');
    $tmpl->assign('spanText', $spanText);
    $tmpl->assign('service', $smburl);
    $tmpl->assign('share', $smbshare);
    $tmpl->assign('actionUrl', $actionUrl);
    $tmpl->assign('id_username', $id_username);
    $tmpl->assign('id_password', $id_password);

    OCP\JSON::success(array("form" => $tmpl->fetchPage()));
}
