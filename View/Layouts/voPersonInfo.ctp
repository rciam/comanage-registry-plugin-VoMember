<?php
/**
 * COmanage Registry Lightbox Layout
 *
 * Portions licensed to the University Corporation for Advanced Internet
 * Development, Inc. ("UCAID") under one or more contributor license agreements.
 * See the NOTICE file distributed with this work for additional information
 * regarding copyright ownership.
 *
 * UCAID licenses this file to you under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with the
 * License. You may obtain a copy of the License at:
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @link          http://www.internet2.edu/comanage COmanage Project
 * @package       registry
 * @since         COmanage Registry v1.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

// As a general rule, all Registry pages are post-login and so shouldn't be cached
header("Expires: Thursday, 10-Jan-69 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, max-age=0, must-revalidate");
header("Pragma: no-cache");
?>
<!DOCTYPE html>
<html>
<head>
  <title><?php print _txt('coordinate') . ': ' . filter_var($title_for_layout,FILTER_SANITIZE_STRING)?></title>
  <head>
  </head>

  <?php
  // cleanse the controller and action strings and insert them into the body classes
  $controller_stripped = preg_replace('/[^a-zA-Z0-9\-_]/', '', $this->params->controller);
  $action_stripped = preg_replace('/[^a-zA-Z0-9\-_]/', '', $this->params->action);
  $bodyClasses = $controller_stripped . ' ' .$action_stripped;

  $redirect_url = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"] . $this->request->here . '/render:norm';
  ?>

  <!-- Body element will only be loaded if we load lightbox as a standalone layout.  -->
  <!-- Otherwise we will find ourselves using the existing body. So we choose to hide the body when not -->
  <!-- in the context of another layout -->
<body class="<?php print $bodyClasses ?>">
<div id="lightboxContent" class="light-box">
  <?php
  // insert the page internal content
  print $this->fetch('content');
  ?>
</div>
</body>
</html>

