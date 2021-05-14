<?php
/**
 * COmanage Registry Voms Member Index View
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
 * @since         VomsMember v1.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */
?>

<script>
  // Observers list
  var accordion_observer = new Array();
  // var filterbox_observer = new Array();

  // Options for the Dropdown Action Menu Observer
  const cmVomsAccordionOptions = {
    attributes: true,
    attributeFilter: ['class']
  };

  function observeFilterVisibility(element, target_element_class, modify_class) {
    accordion_observer[element] = new MutationObserver((mutationList) => { // Use traditional 'for loops' for IE 11
      for(const mutation of mutationList) {
        if (mutation.type === 'attributes') {
          curr_elem = mutation.target;
          elem_children = mutation.target.childNodes;
          $filter_box = $(elem_children).find('.filter-textbox');
          if($(curr_elem).hasClass('ui-state-active')) {
            $filter_box.removeClass(modify_class);
          } else {
            $filter_box.addClass(modify_class);
          }
        }
      }
    });
    accordion_observer[element].observe(element,cmVomsAccordionOptions);
  }


  // https://stackoverflow.com/questions/32383349/detect-value-change-in-input-tag-with-vanilla-javascript-and-mutationobserver
  function observeFilterText(element, property, callback, delay = 0) {
    let elementPrototype = Object.getPrototypeOf(element);
    if (elementPrototype.hasOwnProperty(property)) {
      let descriptor = Object.getOwnPropertyDescriptor(elementPrototype, property);
      Object.defineProperty(element, property, {
        get: function() {
          return descriptor.get.apply(this, arguments);
        },
        set: function () {
          let oldValue = this[property];
          descriptor.set.apply(this, arguments);
          let newValue = this[property];
          if (typeof callback == "function") {
            setTimeout(callback.bind(this, oldValue, newValue), delay);
          }
          return newValue;
        }
      });
    }
  }

  // Reset the text
  function filter_reset(elem) {
    $filter_box = $(elem).siblings('.cert-filter');
    $filter_box.val("");
    $filter_box.trigger("keyup");
  }

  $(function() {

    // Handle Accordion - Filter-textbox toggle
    $('.co-person .person-panel').each( (key, elem) => {
      // Add observer for open/close accordion
      observeFilterVisibility(elem, 'filter-textbox', 'hidden');
      // Add event listener fot text read
      $filter_box_elem = $(elem).find('.cert-filter');
      $filter_box_elem.on("keyup", function () {
        let value = this.value.toLowerCase();
        $roles = $(this).closest('.co-person').find('.roles').children();
        $roles.each(function(index) {
          let $role = $(this);
          let text_payload = $role.text().toLowerCase();
          if(text_payload.includes(value)) {
            // debugger;
            $role.show();
          } else {
            // debugger;
            $role.hide();
          }
        });
      });
    });

    $( ".co-person" ).accordion({
      collapsible: true,
      active     : false,
      heightStyle: "content"
    });

    $("#clearSearchButton").button();
  });

  function toggleVomsList(state) {
    if (state == 'open') {
      $(".co-person" ).accordion( "option", "active", 0 );
      $(".filter-textbox").css("visibility", "visible");
    } else {
      $(".co-person" ).accordion( "option", "active", false );
      $(".filter-textbox").css("visibility", "hidden");
    }
  }
</script>

<?php
// Load CSS and JS Libraries
print $this->Html->css('/VomsMember/css/voms_members');

// Construct and load Page title
$title = ($vv_all) ? _txt('ct.voms_members_all.pl') : _txt('ct.voms_members_my.pl');
$params = array('title' => $title);
print $this->element("pageTitle", $params);

// Add breadcrumbs
print $this->element("coCrumb");
$this->Html->addCrumb(_txt('ct.voms_members.pl'));

// Add top links
$params['topLinks'] = array();

?>
<?php if($vv_permissions['all']): ?>
<div id="sorter" class="listControl">
  <ul>
    <li class="spin">
      <?php
      print $this->Html->link(
        ($vv_all) ? _txt('ct.voms_members_my.pl') : _txt('ct.voms_members_all.pl'),
        array(
          'controller' => 'voms_members',
          'action' => 'index',
          'co' => $this->params['named']['co'],
          'all' => ($vv_all) ? false : true,
        ),
        array('class' => 'notebutton')
      );
      ?>
    </li>
  </ul>
</div>
<?php endif; ?>

<div id="voms_toggle" class="listControl">
  <?php print _txt('fd.toggle.all'); ?>:
  <ul>
    <li><?php print $this->html->link(_txt('fd.open'),'javascript:toggleVomsList(\'open\');'); ?></li>
    <li><?php print $this->html->link(_txt('fd.closed'),'javascript:toggleVomsList(\'closed\');'); ?></li>
  </ul>
</div>
<?php
if($vv_permissions['search']) {
  // Load the top search form
  $fileLocation = LOCAL . DS . 'Plugin' . DS . 'VomsMember' . DS . "View/VomsMembers/search.inc";
  if(file_exists($fileLocation)) {
    include($fileLocation);
  }
}
?>
<div id="voms-name-header" class="listControl" aria-label="<?php print _txt('ct.voms_members.vo_name'); ?>">
  <?php print _txt('ct.voms_members.vo_name'); ?>
</div>
<div id="voms_members_list" class="population-index">
  <?php $i = 0; ?>
  <?php foreach ($vv_voms_list as $vo_name => $certs): ?>
    <div class="co-person line<?php print ($i % 2)+1; ?>">
      <div class="person-panel">
        <div class="person-info">
          <div class="person-info-inner">
            <span class="person-name nameWithEmail">
              <?php
              print $vo_name
              ?>
            </span>
            <span class="person-email">
              <?php
                print '( #' . count($certs) . ' )';
              ?>
            </span>
          </div>
        </div>
        <div class="person-admin">
          <?php
          if($vv_all) {
            $filter_cfg = array(
              'vv_id' => $vo_name . $i . "filterbox",
            );
            print $this->element('VomsMember.filterTxtBox', $filter_cfg);
          }
          ?>
        </div>
        <span class="clearfix"></span>
      </div>
      <div class = "role-panel">
        <div class="roles-title"><?php print _txt('pl.voms_members.cert.abbreviation'); ?></div>
        <div class="roles">
          <?php
          foreach ($certs as $cert) {
            print '<div class = "role">';
            // Print Status
            if(!empty($cert['created']) ) {
              print '<div class = "rolestatus">';
              $created = $this->Time->format($cert['created'], "%F", false, $vv_tz);
              print $created;
              print '</div>';
            }
            print '<div class = "roleinfo">';
            print '<div class = "roletitle">';
            // Subject
            print _txt('pl.voms_members.subjectdn', array($cert['subject']));
            // Issuer
            if(!empty($cert['issuer'])) {
              print '<span class="roleTitleText" style="display:block;">';
              print _txt('pl.voms_members.issuerdn', array($cert['issuer']));
              print '</span>';
            }


            print '<span class="clearfix"></span>';
            print "</div>";  // roletitle
            print "</div>";  // roleinfo
            print "</div>";  // role
          }
          ?>
        </div>
      </div>
    </div>
    <?php $i++; ?>
  <?php endforeach; // $co_people ?>

<!--  --><?php
//  if(empty($co_people)) {
//    // No search results, or there are no people in this CO
//    print('<div id="noResults">' . _txt('rs.search.none') . '</div>');
//    print('<div id="restoreLink">');
//    $args = array();
//    $args['plugin'] = null;
//    $args['controller'] = 'co_people';
//    $args['action'] = 'index';
//    $args['co'] = $cur_co['Co']['id'];
//    print $this->Html->link(_txt('op.search.restore'), $args);
//    print('</div>');
//  }
//  ?>

  <?php print $this->element("pagination"); ?>
  <div class="clearfix"></div>

</div>