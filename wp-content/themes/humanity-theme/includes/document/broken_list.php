<?php

class BrokenList_Command
{
    public function __invoke(): void
    {
        $documents = $this->getAllDocuments();
        $documents_count = \count($documents);
        $missing_documents_count = 0;
        $repaired_documents_count = 0;

        foreach ($documents as $document) {
            $post_id = $document->ID;
            if (!$post_id) {
                continue;
            }

            $private = amnesty_document_is_private($post_id);
            $attachment_id = amnesty_document_get_attachment_id($post_id);

            if ($private) {
                $good_path = amnesty_document_get_private_file_path($attachment_id);
                $wrong_path = amnesty_document_get_public_file_path($attachment_id);
            } else {
                $good_path = amnesty_document_get_public_file_path($attachment_id);
                $wrong_path = amnesty_document_get_private_file_path($attachment_id);
            }

            $admin_url = admin_url("post.php?post=$post_id&action=edit");
            if (!file_exists($good_path)) {
                WP_CLI::warning("File not found at expected path $good_path ($admin_url)");
                $missing_documents_count++;
            }

            $is_at_wrong_path = file_exists($wrong_path);
            if (!$is_at_wrong_path) {
                continue;
            }

            WP_CLI::log("File found at wrong path $wrong_path, trying to repair it");
            $moved = amnesty_document_move_attachment_file($attachment_id, $private);
            if ($moved) {
                WP_CLI::success("File moved to $good_path");
                $repaired_documents_count++;
            } else {
                WP_CLI::warning("File could not be moved to $good_path");
            }
        }

        WP_CLI::log(
            sprintf(
                'Found %s/%s attachments (%s missing - %s should be repaired)',
                $documents_count - $missing_documents_count,
                $documents_count,
                $missing_documents_count,
                $repaired_documents_count
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
