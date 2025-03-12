import { __ } from "@wordpress/i18n";
import { addFilter } from "@wordpress/hooks";
import { Fragment } from "@wordpress/element";
import { InspectorControls } from "@wordpress/block-editor";
import { createHigherOrderComponent } from "@wordpress/compose";
import { PanelBody, SelectControl, ToggleControl } from "@wordpress/components";

const allowedBlocks = [ 'core/button' ];

function addAttributes( settings, name ) {
	if ( ! allowedBlocks.includes( name ) ) {
		return settings;
	}

	return {
		...settings,
		attributes: {
			...settings.attributes,
			btnSize: {
				type: 'string',
				default: 'small',
			},
			icon: {
				type: 'boolean',
				default: false,
			}
		}
	};
}

const addAdvancedControls = createHigherOrderComponent(( BlockEdit ) => {
	return ( props ) => {

		const { name, attributes, setAttributes, isSelected } = props;
		const { btnSize = 'small', icon = false } = attributes;

		if( ! allowedBlocks.includes( name ) ) {
			return(
				<BlockEdit {...props} />
			);
		}

		return (
			<>
				<BlockEdit {...props} />
				{isSelected &&
					<InspectorControls>
						<PanelBody className='btn-settings-panel' title={__('Settings', 'amnestyfr-child-theme')} initialOpen={true} >
							<ToggleControl
								__nextHasNoMarginBottom
								label={__('Icône', 'amnestyfr-child-theme')}
								checked={icon}
								onChange={ (value) => setAttributes( { icon: value} ) }
							/>
							<SelectControl
								__nextHasNoMarginBottom
								label={__('Taille', 'amnestyfr-child-theme')}
								value={btnSize}
								options={[
									{ label: 'Petit', value: 'small'},
									{ label: 'Moyen', value: 'medium'},
									{ label: 'Grand', value: 'large'},
								]}
								onChange={ (value) => setAttributes( {btnSize: value} ) }
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
		const { btnSize, icon } = props.attributes;

		if( ! allowedBlocks.includes( name ) ) {
			return(
				<Block {...props} />
			)
		}

		let className = btnSize ? `btn-size-${btnSize}` : '';
		className += icon ? ' btn-with-icon' : '';

		return (
			<Block { ...props } className={className} />
		);
	};
}, 'addAdvancedControls');

function applyExtraClass( extraProps, blockType, attributes ) {
	if( ! allowedBlocks.includes( blockType.name ) ) {
		return extraProps;
	}
	const { btnSize, icon } = attributes;

	if(btnSize) {
		extraProps.className = extraProps.className ? extraProps.className + ` btn-size-${btnSize}` : `btn-size-${btnSize}`;
	}
	if(icon) {
		extraProps.className = extraProps.className ? extraProps.className + ` btn-with-icon` : `btn-with-icon`;
	}

	return extraProps;
}

addFilter(
	'blocks.registerBlockType',
	'child-theme-extension/btn-settings-attribute',
	addAttributes
);
addFilter(
	'editor.BlockEdit',
	'child-theme-extension/btn-settings-advanced-control',
	addAdvancedControls
);
addFilter(
	'editor.BlockListBlock',
	'child-theme-extension/btn-settings-custom-block-class',
	addCustomClassToBlock
);
addFilter(
	'blocks.getSaveContent.extraProps',
	'child-theme-extension/btn-settings-applyExtraClass',
	applyExtraClass
);
