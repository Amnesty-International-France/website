# Prismic Import

This plugin create a wp-cli command to migrate data from the Amnesty International France Prismic Repository to Wordpress with humanity-theme.

## Usage

First, you need to fetch all documents in the json format.

`wp prismic-import fetch data_prismic.json`

Documents will be ordered from the oldest to the newest to assure links between them. (some slices with link need to have post created in wordpress because their ids are needed).

You can limit the number of documents with the option `--limit`.

`wp prismic-import fetch data_prismic_100.json --limit=100` will fetch 100 documents.

