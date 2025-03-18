import { __ } from "@wordpress/i18n";
import {
	useBlockProps,
	InnerBlocks,
	InspectorControls,
} from "@wordpress/block-editor";
import { PanelBody, ToggleControl, TextControl } from "@wordpress/components";
import "./style.scss";

export default function Edit({ attributes, setAttributes }) {
	const { title, showTitle } = attributes;

	return (
		<div {...useBlockProps({ className: "wp-block-small-section" })}>
			<InspectorControls>
				<PanelBody title={__("Paramètres du titre", "small-section")}>
					<ToggleControl
						label={__("Afficher le titre", "small-section")}
						checked={showTitle}
						onChange={(value) => setAttributes({ showTitle: value })}
					/>
					{showTitle && (
						<TextControl
							label={__("Titre", "small-section")}
							value={title}
							placeholder={__("Saisissez un titre", "small-section")}
							onChange={(value) => setAttributes({ title: value })}
						/>
					)}
				</PanelBody>
			</InspectorControls>

			<div className="wp-block-small-section-content">
				{showTitle && <h3>{title}</h3>}
				<div className="wp-block-small-section-inner-blocks-container">
					<InnerBlocks />
				</div>
			</div>
		</div>
	);
}
