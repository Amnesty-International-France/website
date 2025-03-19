import { __ } from "@wordpress/i18n";
import {
	useBlockProps,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from "@wordpress/block-editor";
import { PanelBody, Button, TextControl } from "@wordpress/components";
import "./style.scss";

export default function Edit({ attributes, setAttributes }) {
	const { title, files } = attributes;

	const updateTitle = (newTitle) => {
		setAttributes({ title: newTitle });
	};

	const getFileTypeLabel = (mimeType) => {
		const typeMapping = {
			"application/pdf": "PDF",
			"application/zip": "ZIP",
			"image/jpeg": "JPEG",
			"image/png": "PNG",
			"text/plain": "Texte",
		};
		return typeMapping[mimeType] || "Fichier";
	};

	const addFile = (file) => {
		const fileName = file.title || file.name;
		const fileType = getFileTypeLabel(file.mime || file.type);
		const fileSize = file.filesizeInBytes || file.size;

		const newFiles = [
			...(files || []),
			{
				id: file.id,
				name: fileName,
				url: file.url,
				type: fileType,
				size: (fileSize / 1024).toFixed(2) + " kb",
			},
		];
		setAttributes({ files: newFiles });
	};

	const removeFile = (id) => {
		const newFiles = files.filter((file) => file.id !== id);
		setAttributes({ files: newFiles });
	};

	return (
		<div {...useBlockProps({ className: "wp-block-download-go-further" })}>
			<InspectorControls>
				<PanelBody
					title={__("Paramètres du bloc", "download-go-further")}
					initialOpen={true}
				>
					<TextControl
						label={__("Titre", "download-go-further")}
						value={title}
						onChange={updateTitle}
						placeholder={__("Entrez un titre...", "download-go-further")}
					/>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={addFile}
							allowedTypes={["application/pdf", "application/zip"]}
							render={({ open }) => (
								<Button onClick={open} variant="primary">
									{__("Ajouter un fichier", "download-go-further")}
								</Button>
							)}
						/>
					</MediaUploadCheck>
					<ul>
						{files &&
							files.map((file) => (
								<li
									key={file.id}
									className="wp-block-download-go-further-download-item"
								>
									<span>
										{file.name} ({file.type}, {file.size})
									</span>
									<Button
										onClick={() => removeFile(file.id)}
										variant="tertiary"
									>
										{__("Supprimer", "download-go-further")}
									</Button>
								</li>
							))}
					</ul>
				</PanelBody>
			</InspectorControls>
			{title && (
				<div className="wp-block-download-go-further-title">
					<h3>{title}</h3>
				</div>
			)}
			<ul className="wp-block-download-go-further-download-list">
				{files &&
					files.map((file) => (
						<li
							key={file.id}
							className="wp-block-download-go-further-download-item"
						>
							<p className="wp-block-download-go-further-download-item-text">
								{file.name} ({file.type}, {file.size})
							</p>
							<a href={file.url} download>
								<Button
									className="wp-block-download-go-further-download-item-button"
									variant="secondary"
								>
									<svg
										xmlns="http://www.w3.org/2000/svg"
										viewBox="0 0 24 24"
										fill="currentColor"
										class="size-6"
									>
										<path
											fill-rule="evenodd"
											d="M19.5 21a3 3 0 0 0 3-3V9a3 3 0 0 0-3-3h-5.379a.75.75 0 0 1-.53-.22L11.47 3.66A2.25 2.25 0 0 0 9.879 3H4.5a3 3 0 0 0-3 3v12a3 3 0 0 0 3 3h15Zm-6.75-10.5a.75.75 0 0 0-1.5 0v4.19l-1.72-1.72a.75.75 0 0 0-1.06 1.06l3 3a.75.75 0 0 0 1.06 0l3-3a.75.75 0 1 0-1.06-1.06l-1.72 1.72V10.5Z"
											clip-rule="evenodd"
										/>
									</svg>
									{__("Télécharger", "download-go-further")}
								</Button>
							</a>
						</li>
					))}
			</ul>
		</div>
	);
}
