import { __ } from "@wordpress/i18n";
import { useBlockProps, InspectorControls } from "@wordpress/block-editor";
import { PanelBody, SelectControl } from "@wordpress/components";
import { useEffect, useState } from "react";
import "./style.scss";

const Edit = (props) => {
	const { attributes, setAttributes } = props;

	const [posts, setPosts] = useState([]);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		fetch("/wp-json/wp/v2/posts")
			.then((response) => response.json())
			.then((data) => {
				setPosts(data);
				setLoading(false);
			})
			.catch((error) => {
				console.error("Erreur de récupération des posts", error);
				setLoading(false);
			});
	}, []);

	const handlePostSelect = (selectedPostId) => {
		const selectedPost = posts.find(
			(post) => post.id === parseInt(selectedPostId),
		);
		if (selectedPost) {
			setAttributes({
				link: selectedPost.link,
				text: selectedPost.title.rendered,
			});
		}
	};

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={__("Paramètres du lien", "read-also")}
					initialOpen={true}
				>
					{loading ? (
						<p>{__("Chargement des posts...", "read-also")}</p>
					) : (
						<SelectControl
							label={__("Sélectionner un post", "read-also")}
							value={attributes.link}
							options={[
								{ label: __("Choisir un post", "read-also"), value: "" },
								...posts.map((post) => ({
									label: post.title.rendered,
									value: post.id.toString(),
								})),
							]}
							onChange={handlePostSelect}
						/>
					)}
				</PanelBody>
			</InspectorControls>

			<div {...useBlockProps({ className: "read-also-block" })}>
				<p>
					{__("À lire aussi", "read-also")}:
					<a href={attributes.link} target="_blank" rel="noopener noreferrer">
						{attributes.text || __("Sélectionnez un post", "read-also")}
					</a>
				</p>
			</div>
		</>
	);
};

export default Edit;
