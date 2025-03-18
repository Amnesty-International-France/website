import { useBlockProps } from "@wordpress/block-editor";
import { __ } from "@wordpress/i18n";

export default function save({ attributes }) {
	return (
		<div {...useBlockProps.save()} className="read-also-block">
			<p>
				{__("À lire aussi", "read-also")}:
				<a href={attributes.link} target="_blank" rel="noopener noreferrer">
					{attributes.text || __("Sélectionnez un post", "read-also")}
				</a>
			</p>
		</div>
	);
}
