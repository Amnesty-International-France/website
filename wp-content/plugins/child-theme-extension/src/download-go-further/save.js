import { __ } from "@wordpress/i18n";
import { useBlockProps, RichText } from "@wordpress/block-editor";
import "./style.scss";

export default function Save({ attributes }) {
	const { title, files } = attributes;

	return (
		<div {...useBlockProps.save({ className: "wp-block-download-go-further" })}>
			{title && <h3>{title}</h3>}
			<ul>
				{files &&
					files.map((file) => (
						<li key={file.id} className="download-item">
							<span>{file.name}</span>
							<a href={file.url} download>
								<button className="download-button">
									{__("Télécharger", "download-go-further")}
								</button>
							</a>
						</li>
					))}
			</ul>
		</div>
	);
}
