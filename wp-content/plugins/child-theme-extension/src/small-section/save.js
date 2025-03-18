import { useBlockProps, InnerBlocks } from "@wordpress/block-editor";

export default function save({ attributes }) {
	const { title, showTitle } = attributes;

	return (
		<div {...useBlockProps.save({ className: "wp-block-small-section" })}>
			<div class="wp-block-small-section-content">
				{showTitle && <h3>{title}</h3>}
				<div className="wp-block-small-section-inner-blocks-container">
					<InnerBlocks.Content />
				</div>
			</div>
		</div>
	);
}
