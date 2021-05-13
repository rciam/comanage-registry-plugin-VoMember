<?php
/**
 * COmanage Voms Members Plugin Language File
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
 * @package       registry-plugin
 * @since         VomsMember v1.0.0
 * @license       Apache License, Version 2.0 (http://www.apache.org/licenses/LICENSE-2.0)
 */

global $cm_lang, $cm_texts;

// When localizing, the number in format specifications (eg: %1$s) indicates the argument
// position as passed to _txt.  This can be used to process the arguments in
// a different order than they were passed.

$cm_voms_member_texts['en_US'] = array(
  //  Titles per Controller
  'ct.voms_members.pl'                    => 'VOMS External Memberships',
  'ct.voms_members_all.pl'                => 'VOMS External Memberships All',
  'ct.voms_members_my.pl'                 => 'My VOMS External Memberships',
  'ct.voms_members.1'                     => 'VOMS External Membership',

  // Plugin Texts
  'pl.voms_members.cert.1'                => 'Certificate',
  'pl.voms_members.cert.pl'               => 'Certificates',
  'pl.voms_members.cert.abbreviation'     => 'Cert',
  'pl.voms_members.subjectdn'             => '<b>Subject DN:</b> %1$s',
  'pl.voms_members.issuerdn'              => '<b>Issuer DN:</b> %1$s',

  // Search Texts
  'sh.voms_members.vomsid'                => 'VOMs Name',
  'sh.voms_members.subjectdn'             => 'Subject DN',
  'sh.voms_members.issuerdn'              => 'Issuer DN',
  'sh.voms_members.all'                   => 'ALL',

  // Error
  'er.voms_members.blackhauled'           => 'Session had expired',
);