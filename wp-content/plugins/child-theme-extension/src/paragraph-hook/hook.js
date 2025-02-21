import { __ } from "@wordpress/i18n";
import { addFilter } from "@wordpress/hooks";
import { Fragment } from "@wordpress/element";
import { InspectorControls } from "@wordpress/block-editor";
import { createHigherOrderComponent } from "@wordpress/compose";
import { PanelBody, ToggleControl } from "@wordpress/components";

const allowedBlocks = [ 'core/paragraph' ];

function addAttributes( settings, name ) {

	// Ne rien faire si ce n'est pas notre bloc
	if ( ! allowedBlocks.includes( name ) ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			chapo: {
				type: 'boolean',
				default: false,
			}
		}
	};
}

const addAdvancedControls = createHigherOrderComponent(( BlockEdit ) => {
	return ( props ) => {

		const { name, attributes, setAttributes, isSelected } = props;
		const { chapo = false } = attributes;

		if( ! allowedBlocks.includes( name ) ) {
			return(
				<BlockEdit {...props} />
			);
		}

		return (
			<>
				<BlockEdit {...props} />
				{isSelected &&
					<InspectorControls group='styles'>
						<PanelBody title='Child Theme Extension' initialOpen={false} >
							<ToggleControl
								__nextHasNoMarginBottom
								label={ __('Chapo', 'child-theme-extension') }
								checked={chapo}
								onChange={ ( value ) => setAttributes( { chapo: value } ) }
							/>
						</PanelBody>
					</InspectorControls>
				}
			</>
		);
	};
}, 'addAdvancedControls');

const addCustomClassToBlock = createHigherOrderComponent( ( Block ) => {
	return ( props ) => {

		const { name } = props;
		const { chapo } = props.attributes;

		// Si ce n'est pas le bon bloc, on quitte
		if( ! allowedBlocks.includes( name ) ) {
			return(
				<Block {...props} />
			)
		}

		// Ajout de la classe
		const className = chapo ? 'is-chapo' : '';

		// Ajout de l'élément dans l'inspecteur
		return (
			<Block { ...props } className={className} />
		);
	};
}, 'addAdvancedControls');

function applyExtraClass( extraProps, blockType, attributes ) {
	if( ! allowedBlocks.includes( blockType.name ) ) {
		return extraProps;
	}
	const { chapo } = attributes;

	if(chapo) {
		extraProps.className = extraProps.className ? extraProps.className + ' is-chapo' : 'is-chapo';
	}

	return extraProps;
}

addFilter(
	'blocks.registerBlockType',
	'child-theme-extension/chapo-attribute',
	addAttributes
);
addFilter(
	'editor.BlockEdit',
	'child-theme-extension/chapo-advanced-control',
	addAdvancedControls,
	11
);
addFilter(
	'editor.BlockListBlock',
	'child-theme-extension/custom-block-class',
	addCustomClassToBlock
);
addFilter(
	'blocks.getSaveContent.extraProps',
	'child-theme-extension/applyExtraClass',
	applyExtraClass
);
