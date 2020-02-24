/**
 * External dependencies
 */
import Masonry from 'react-masonry-css';

/**
 * Internal dependencies
 */
import InspectorControls from './inspector-controls';
import SinglePost from './single-post';

const PostsList = ( {
	attributes,
	setAttributes,
	recentPosts,
	imageSizeOptions,
} ) => {
	const { style, columns } = attributes;

	let columnSpan = 12;

	if ( style === 'grid' ) {
		/*
		 * This works well for the design if we have a maximum of 4 columns. It would not work
		 * so well for 5 and 7 columns for example. Something to keep in mind if the max number of columns
		 * increase above 4.
		 */
		columnSpan = Math.floor( 12 / columns );
	}

	return (
		<>
			<InspectorControls
				attributes={ attributes }
				setAttributes={ setAttributes }
				imageSizeOptions={ imageSizeOptions }
			/>

			{ ( style === 'grid' || style === 'list' ) && (
				<div className={ `mdc-layout-grid layout-${ style }` }>
					<div className="mdc-layout-grid__inner">
						{ recentPosts.map( ( post, postIndex ) => {
							const props = { post, postIndex, style, attributes };
							return (
								<div
									key={ postIndex }
									className={ `mdc-layout-grid__cell--span-${ columnSpan }` }
								>
									<SinglePost { ...props } />
								</div>
							);
						} ) }
					</div>
				</div>
			) }

			{ style === 'masonry' && (
				<Masonry
					breakpointCols={ columns }
					className={ `masonry-grid layout-${ style }` }
					columnClassName="masonry-grid_column"
				>
					{ recentPosts.map( ( post, postIndex ) => {
						const props = { post, postIndex, style, attributes };
						return (
							<div key={ postIndex }>
								<SinglePost { ...props } />
							</div>
						);
					} ) }
				</Masonry>
			) }
		</>
	);
};

export default PostsList;
