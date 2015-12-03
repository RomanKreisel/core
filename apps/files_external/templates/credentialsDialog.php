<?php
/**
 * ownCloud
 *
 * @author Juan Pablo Villafañez Ramos <jvillafanez@owncloud.com>
 * @author Jesus Macias Portela <jesus@owncloud.com>
 * @copyright (C) 2014 ownCloud, Inc.
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
?>
<div id="files_external_div_form" title="<?php p($l->t('Credentials validation')) ?>" style="text-align:center;">
  <div>
    <span><?php echo $_['spanText'] ?></span>
    <br/>
    <span><?php p($l->t('Service url: %s', $_['service'])) ?></span>
    <br/>
    <span><?php p($l->t('Share: %s', $_['share'])) ?></span>
    <form method="post" action="<?php p($_['actionUrl']) ?>">
      <input type="text" name="<?php p($_['id_username']) ?>" placeholder="<?php p($l->t('Username')) ?>"/>
      <input type="password" name="<?php p($_['id_password']) ?>" placeholder="<?php p($l->t('Password')) ?>"/>
    </form>
  </div>
</div>
