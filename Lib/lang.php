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

$cm_vo_member_texts['en_US'] = array(
  //  Titles per Model (The framework uses the Model name to construct the title.
  'ct.vo_members.pl'                    => 'External VO Memberships',
  'ct.voms_members.pl'                  => 'External VO Memberships',
  'ct.vo_members.1'                     => 'External VO Membership',
  'ct.voms_members.1'                   => 'External VO Membership',

  'ct.vo_members_all.pl'                => 'All External VO Memberships',
  'ct.vo_members_my.pl'                 => 'My External VO Memberships',
  'ct.vo_members.vo'                    => 'Virtual Organization',

  // Plugin Texts
  'pl.vo_members.cert.1'                => 'Certificate',
  'pl.vo_members.cert.pl'               => 'Certificates',
  'pl.vo_members.cert.abbreviation'     => 'Cert',
  'pl.vo_members.subjectdn'             => '<b>Subject DN:</b> %1$s',
  'pl.vo_members.issuerdn'              => '<b>Issuer DN:</b> %1$s',
  'pl.vo_members.inregistry'            => 'In Registry',

  // Configuration View
  'pl.vo_members.ops'                    => 'Operations Portal',
  'pl.vo_members.place.holder'           => 'Placeholder',
  'pl.vo_members.base.url'               => 'Base URL',
  'pl.vo_members.base.url.desc'          => 'Base Url including http(s):// protocol prefix',
  'pl.vo_members.endpoint'               => 'API endpoint',
  'pl.vo_members.endpoint.ops.desc'      => 'API endpoint for Operations Portal',
  'pl.vo_members.authkey'                => 'Authorization Key',

  // Search Texts
  'sh.vo_members.vo_id'                 => 'VO Name',
  'sh.vo_members.subjectdn'             => 'Subject DN',
  'sh.vo_members.issuerdn'              => 'Issuer DN',
  'sh.vo_members.all'                   => 'ALL',

  // Shell Text
  'sh.sync.no.config'                   => 'VoMember is not configured',
  'sh.sync.arg.coid'                    => 'Numeric CO ID to run tasks for (all COs if not specified)',

  // Error
  'er.vo_members.blackhauled'           => 'Session had expired',
  'er.vo_members.db.failed'             => 'Database Save failed',
  'er.vo_members.notfound'              => '%1$s Not Found',
  'er.vo_members.http.failed'           => 'Request Failed',
);
