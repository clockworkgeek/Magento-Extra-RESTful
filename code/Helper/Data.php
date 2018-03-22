<?php

/**
 * @author Daniel Deady <daniel@clockworkgeek.com>
 * @license MIT
 */
class Clockworkgeek_Extrarestful_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * Inspired by image file upload for <code>POST /api/rest/products/:id/images</code>
     *
     * Image field must be an array with <code>file_content</code> and <code>file_name</code> entries.
     * Content is always base64 encoded.
     * <code>file_mime_type</code> is not used and finfo is consulted instead to work it out locally.
     * Only image mime types are accepted.
     * Mime type is not compared to file extension.
     *
     * @param array $field
     * @return string|boolean One error message, if found
     */
    public function validateImageField(array $field)
    {
        if (!isset($field['file_content'])) {
            return 'Upload field must have a file_content';
        }
        if (!isset($field['file_name'])) {
            return 'Upload field must have a file_name';
        }
        if (!is_string($field['file_content']) || preg_match('/[^a-z0-9\/+=/i', $field['file_content'])) {
            return 'File content must be a base64 string';
        }
        if (is_string($field['file_name']) && strpbrk($field['file_name'], '/\\:') !== false) {
            return 'File name cannot contain any directory parts';
        }
        $content = base64_decode($field['file_content'], true);
        if ($content === false) {
            return 'File content must be a base64 string';
        }
        $mimetype = (new finfo(FILEINFO_MIME_TYPE))->buffer($content);
        if (substr($mimetype, 0, 6) !== 'image/') {
            return 'File is not a recognised image type';
        }
        return false;
    }

    /**
     * Decode a validated image field and save to <code>$dir</code>
     *
     * @param array $field
     * @param string $dir
     * @return string The actual filename used, not including <code>$dir</code>
     */
    public function uploadImageField(array $field, $dir)
    {
        $content = base64_decode($field['file_content']);
        // make safe of unusual chars
        $filename = Mage_Core_Model_File_Uploader::getCorrectFileName($field['file_name']);
        // do not overwrite existing files, insert a number if necessary
        $filename = Mage_Core_Model_File_Uploader::getNewFileName($dir.DS.$filename);

        $io = new Varien_Io_File();
        $io->createDestinationDir($dir);
        $io->write($dir.DS.$filename, $content);

        return $filename;
    }
}
