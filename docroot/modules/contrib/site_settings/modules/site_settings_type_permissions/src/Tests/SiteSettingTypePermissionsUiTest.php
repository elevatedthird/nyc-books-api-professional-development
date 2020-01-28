<?php

namespace Drupal\site_settings_type_permissions\Tests;

use Drupal\Tests\field_ui\Traits\FieldUiTestTrait;
use Drupal\simpletest\WebTestBase;

/**
 * Tests the Site Settings type permissions.
 *
 * @group SiteSettings
 */
class SiteSettingTypePermissionsUiTest extends WebTestBase {

  use FieldUiTestTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'site_settings',
    'site_settings_sample_data',
    'site_settings_type_permissions',
    'field_ui',
    'user',
  ];

  /**
   * Tests site settings type permissions for editor users.
   */
  public function testSiteSettingsTypePermissions() {
    // Create an editor user for test.
    $editor = $this->drupalCreateUser([
      'administer site configuration',
      'access site settings overview',
      'view published test_multiple_entries_and_fields site setting entities',
      'view unpublished test_multiple_entries_and_fields site setting entities',
      'create test_multiple_entries_and_fields site setting',
      'edit test_multiple_entries_and_fields site setting',
      'delete test_multiple_entries_and_fields site setting',
      'view published test_plain_text site setting entities',
      'view unpublished test_plain_text site setting entities',
      'create test_plain_text site setting',
      'edit test_plain_text site setting',
      'delete test_plain_text site setting',
    ]);

    $this->drupalLogin($editor);

    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Make sure the fieldsets match.
    $this->assertRaw('<strong>Other</strong>', 'Other fieldset is visible');
    $this->assertNoRaw('<strong>Images</strong>', 'Images fieldset is not visible');

    // Make sure the test plain text is as expected.
    $this->assertText('Test plain text', 'Test plain text value is visible');

    // Make sure the test multiple entries and fields contents are as expected.
    $this->assertText('Test multiple entries and fields name 1', 'Test multiple entries and fields content 1 is visible');
    $this->assertText('Test multiple entries and fields name 2', 'Test multiple entries and fields content 2 is visible');

    $this->drupalLogout();

    // Create an edit only user for test.
    $edit_only = $this->drupalCreateUser([
      'administer site configuration',
      'access site settings overview',
      'edit test_multiple_entries_and_fields site setting',
      'edit test_plain_text site setting',
    ]);

    $this->drupalLogin($edit_only);

    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Make sure the fieldsets match.
    $this->assertRaw('<strong>Other</strong>', 'Other fieldset is visible');
    $this->assertNoRaw('<strong>Images</strong>', 'Images fieldset is not visible');

    // Make sure the test plain text is as expected.
    $this->assertText('Test plain text', 'Test plain text value is visible');

    // Make sure the test multiple entries and fields contents are as expected.
    $this->assertText('Test multiple entries and fields name 1', 'Test multiple entries and fields content 1 is visible');
    $this->assertText('Test multiple entries and fields name 2', 'Test multiple entries and fields content 2 is visible');

    // Click edit link.
    $this->clickLink('Edit', 0);
    $this->assertText('Edit Test plain text name', 'The plain text edit page is visible');

    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Click edit link.
    $this->clickLink('Edit', 1);
    $this->assertText('Edit Test multiple entries and fields name 1', 'The test multiple entries edit title is visible');

    $this->drupalLogout();

    // Create a creator user for test.
    $creator = $this->drupalCreateUser([
      'administer site configuration',
      'access site settings overview',
      'create test_multiple_entries_and_fields site setting',
      'create test_plain_text site setting',
    ]);

    $this->drupalLogin($creator);

    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Make sure the test plain text is not expected.
    $this->assertNoText('Test plain text', 'Test plain text is not visible');

    // Make sure the test multiple entries and fields contents are as expected.
    $this->assertText('Test multiple entries and fields', 'Test multiple entries and fields is visible');

    // Click add link.
    $this->clickLink('Create setting', 0);

    // Make sure the test multiple entries and fields create page is as
    // expected.
    $this->assertText('Test multiple entries and fields', 'Test multiple entries and fields create page is visible');

    // Open the site settings list page.
    $this->drupalGet('admin/structure/site_setting_entity/add/test_plain_text');
    $this->assertText('Access denied', 'Access is denied for the not multiple type has created.');

    $this->drupalLogout();

    // Create a remover user for test.
    $remover = $this->drupalCreateUser([
      'administer site configuration',
      'access site settings overview',
      'view published test_multiple_entries_and_fields site setting entities',
      'view unpublished test_multiple_entries_and_fields site setting entities',
      'view published test_plain_text site setting entities',
      'view unpublished test_plain_text site setting entities',
      'delete test_multiple_entries_and_fields site setting',
      'delete test_plain_text site setting',
    ]);

    $this->drupalLogin($remover);

    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Make sure the test plain text is expected.
    $this->assertText('Test plain text', 'Test plain text is not visible');

    // Make sure the test multiple entries and fields contents are as expected.
    $this->assertText('Test multiple entries and fields', 'Test multiple entries and fields is visible');

    // Click delete link.
    $this->clickLink('Delete', 0);

    // Make sure the test plain text delete page is visible.
    $this->assertText('This action cannot be undone.', 'Test plain text delete page is visible');

    // Click delete link.
    $this->drupalPostForm($this->getUrl(), [], 'Delete');

    // Make sure the test plain text is not expected.
    $this->assertNoText('Test plain text value', 'Test plain text is not visible');

    $this->drupalLogout();

    // Login creator:
    $this->drupalLogin($creator);

    // Open the site settings list page.
    $this->drupalGet('admin/content/site-settings');

    // Make sure the test plain text is expected.
    $this->assertText('Test plain text', 'Test plain text is visible after deleted a entity');
  }

}
