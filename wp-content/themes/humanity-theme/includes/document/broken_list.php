<?php

class BrokenList_Command
{
    public function __invoke(): void
    {
        $documents = $this->getAllDocuments();
        $documents_count = \count($documents);
        $missing_documents_count = 0;

        foreach ($documents as $document) {
            $post_id = $document->ID;
            if (!$post_id) {
                continue;
            }

            $private = amnesty_document_is_private($post_id);
            $attachment_id = amnesty_document_get_attachment_id($post_id);

            $path = $private ? amnesty_document_get_private_file_path($attachment_id) : amnesty_document_get_public_file_path($attachment_id);
            $admin_url = admin_url("post.php?post=$post_id&action=edit");
            if (file_exists($path)) {
                WP_CLI::warning("File $path does not exist ($admin_url)");
                $missing_documents_count++;
            }
        }

        WP_CLI::log(sprintf('Found %s/%s attachments (%s missing)', $documents_count - $missing_documents_count, $documents_count, $missing_documents_count));
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
