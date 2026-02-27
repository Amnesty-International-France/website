<?php

class BrokenList_Command
{
    public function __invoke(): void
    {
        $documents = $this->getAllDocuments();
        $documentsCount = \count($documents);
        $missingDocumentsCount = 0;
        $repairedDocumentsCount = 0;

        foreach ($documents as $document) {
            $postId = $document->ID;
            if (!$postId) {
                continue;
            }

            $private = amnesty_document_is_private($postId);
            $attachment_id = amnesty_document_get_attachment_id($postId);

            if ($private) {
                $good_path = amnesty_document_get_private_file_path($attachment_id);
                $wrong_path = amnesty_document_get_public_file_path($attachment_id);
            } else {
                $good_path = amnesty_document_get_public_file_path($attachment_id);
                $wrong_path = amnesty_document_get_private_file_path($attachment_id);
            }

            $adminUrl = admin_url("post.php?post=$postId&action=edit");
            if (!file_exists($good_path)) {
                WP_CLI::warning("File not found at expected path $good_path ($adminUrl)");
                $missingDocumentsCount++;
            }

            $is_at_wrong_path = file_exists($wrong_path);
            if (!$is_at_wrong_path) {
                continue;
            }

            WP_CLI::log("File found at wrong path $wrong_path, trying to repair it");
            $moved = amnesty_document_move_attachment_file($attachment_id, $private);
            if ($moved) {
                WP_CLI::success("File moved to $good_path");
                $repairedDocumentsCount++;
            } else {
                WP_CLI::warning("File could not be moved to $good_path");
            }
        }

        WP_CLI::log(
            sprintf(
                'Found %s/%s attachments (%s missing - %s should be repaired)',
                $documentsCount - $missingDocumentsCount,
                $documentsCount,
                $missingDocumentsCount,
                $repairedDocumentsCount
            )
        );
    }

    private function getAllDocuments(): array
    {
        $args = [
            'post_type' => 'document',
            'numberposts' => -1,
            'post_status' => null,
            'post_parent' => null,
        ];

        return get_posts($args);
    }
}

WP_CLI::add_command('document-broken-list', new BrokenList_Command());
