<?php

class BrokenList_Command
{
    public function __invoke(): void
    {
        $documents = $this->getAllDocuments();
        $documentsCount = \count($documents);
        $missingDocumentsCount = 0;

        foreach ($documents as $document) {
            $postId = $document->ID;
            if (!$postId) {
                continue;
            }

            $private = amnesty_document_is_private($postId);
            $attachment_id = amnesty_document_get_attachment_id($postId);

            $path = $private ? amnesty_document_get_private_file_path($attachment_id) : amnesty_document_get_public_file_path($attachment_id);
            $adminUrl = admin_url("post.php?post=$postId&action=edit");
            if (file_exists($path)) {
                WP_CLI::warning("File $path does not exist ($adminUrl)");
                $missingDocumentsCount++;
            }
        }

        WP_CLI::log(sprintf('Found %s/%s attachments (%s missing)', $documentsCount - $missingDocumentsCount, $documentsCount, $missingDocumentsCount));
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
