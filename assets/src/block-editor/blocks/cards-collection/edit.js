/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import {
	useEffect,
	useLayoutEffect,
	useRef,
	useState,
} from '@wordpress/element';

/**
 * Internal dependencies
 */
import CardsCollectionInspectorControls from './components/cards-collection-inspector-controls';
import './editor.css';
import { CARD_ATTRIBUTES_VALUE } from './constants';
import VerticalCardLayout from '../card/components/vertical-card-layout';
import HorizontalCardLayout from '../card/components/horizontal-card-layout';
import FocusedCardControls from './components/focused-card-controls';
import getColumnSpan from './utils/get-column-span';
import Cards from './components/cards';

/**
 * Card Collections Edit component.
 *
 * @param {Object} props - Component props.
 *
 * @return {Function} Function returning the HTML markup for the component.
 */
const Edit = props => {
	const { attributes, setAttributes, className } = props;
	const {
		style,
		columns,
		gutter,
		cardsProps,
		numberOfCards,
		contentLayout,
		cornerRadius,
		outlined,
		displayTitle,
		displaySecondaryText,
		displayImage,
		displaySupportingText,
		displayActions,
		displaySecondaryActionButton,
		allowIndividualStyleOverride,
		allowIndividualContentOverride,
	} = attributes;
	const [ cards, setCards ] = useState( [] );
	const inspectorControlsProps = {
		...props,
	};
	const [ cardsFocus, setCardsFocus ] = useState( [] );
	const columnSpan = getColumnSpan( style, columns );

	/**
	 * @param {string} attributeName - Attribute name.
	 * @param {string} attributeValue - Attribute value.
	 * @param {number} cardIndex - Card index
	 */
	/* istanbul ignore next */
	const setter = ( attributeName, attributeValue, cardIndex ) => {
		const newCardsProps = [ ...cardsProps ];

		if (
			typeof newCardsProps[ cardIndex ] !== 'undefined' &&
			newCardsProps[ cardIndex ][ attributeName ] !== 'undefined'
		) {
			newCardsProps[ cardIndex ][ attributeName ] = attributeValue;
		}

		setAttributes( {
			cardsProps: newCardsProps,
		} );
	};

	inspectorControlsProps.attributes.setter = setter;

	const isInitialMount = useRef( true );

	useEffect(
		() => {
			if ( isInitialMount.current ) {
				isInitialMount.current = false;
			} else {
				const newCardsProps = [ ...cardsProps ];
				for ( let index = 0; index < newCardsProps.length; index++ ) {
					if ( allowIndividualContentOverride === false ) {
						newCardsProps[ index ].displayTitle = displayTitle;
						newCardsProps[ index ].displaySecondaryText = displaySecondaryText;
						newCardsProps[ index ].displayImage = displayImage;
						newCardsProps[
							index
						].displaySupportingText = displaySupportingText;
						newCardsProps[ index ].displayActions = displayActions;
						newCardsProps[
							index
						].displaySecondaryActionButton = displaySecondaryActionButton;
					}

					if ( allowIndividualStyleOverride === false ) {
						newCardsProps[ index ].contentLayout = contentLayout;
						newCardsProps[ index ].cornerRadius = cornerRadius;
						newCardsProps[ index ].outlined = outlined;
					}
				}
				setAttributes( {
					cardsProps: newCardsProps,
				} );
			}
		},
		// eslint-disable-next-line react-hooks/exhaustive-deps
		[
			contentLayout,
			cornerRadius,
			outlined,
			displayTitle,
			displaySecondaryText,
			displayImage,
			displaySupportingText,
			displayActions,
			displaySecondaryActionButton,
			allowIndividualStyleOverride,
			allowIndividualContentOverride,
		]
	);

	// This is to be run after every render.
	/* istanbul ignore next */
	useEffect( () => {
		const editorWrapper = document.getElementsByClassName(
			'editor-styles-wrapper'
		);

		if ( editorWrapper.length === 1 ) {
			editorWrapper[ 0 ].addEventListener( 'click', onClickOutsideCard, true );
		}

		return () => {
			if ( editorWrapper.length === 1 ) {
				editorWrapper[ 0 ].removeEventListener(
					'click',
					onClickOutsideCard,
					true
				);
			}
		};
	}, [] );

	useLayoutEffect(
		() => {
			const items = [];
			for ( let cardIndex = 0; cardIndex < numberOfCards; cardIndex++ ) {
				let baseProps = { ...CARD_ATTRIBUTES_VALUE };
				let cardsPropsHasIncreased = false;
				if ( numberOfCards <= cardsProps.length ) {
					baseProps = { ...cardsProps[ cardIndex ] };
				} else {
					const cardAttributes = { ...CARD_ATTRIBUTES_VALUE };
					if ( allowIndividualContentOverride === false ) {
						cardAttributes.displayTitle = displayTitle;
						cardAttributes.displaySecondaryText = displaySecondaryText;
						cardAttributes.displayImage = displayImage;
						cardAttributes.displaySupportingText = displaySupportingText;
						cardAttributes.displayActions = displayActions;
						cardAttributes.displaySecondaryActionButton = displaySecondaryActionButton;
					}

					if ( allowIndividualStyleOverride === false ) {
						cardAttributes.contentLayout = contentLayout;
						cardAttributes.cornerRadius = cornerRadius;
						cardAttributes.outlined = outlined;
					}
					cardsProps.push( cardAttributes );
					cardsPropsHasIncreased = true;
				}

				if ( cardsPropsHasIncreased ) {
					const newCardsProps = [ ...cardsProps ];

					setAttributes( {
						cardsProps: newCardsProps,
					} );
				}

				const cardProps = {
					cardIndex,
					setAttributes,
					setter,
					isEditMode: true,
					...baseProps,
				};

				const desktopGutter = gutter.desktop || 24;

				const cardStyleProp = {
					marginBottom: `${ desktopGutter }px`,
				};

				items.push(
					<div
						key={ cardIndex }
						data-card-index={ cardIndex }
						className={ classnames(
							'card-container',
							{
								'card-container-focused':
									cardsFocus[ cardIndex ] !== undefined
										? cardsFocus[ cardIndex ]
										: false,
							},
							{
								[ `mdc-layout-grid__cell--span-${ columnSpan }` ]:
									style === 'grid' || style === 'list',
							}
						) }
						style={ style === 'masonry' ? cardStyleProp : undefined }
						onFocus={ () => onCardFocus( cardIndex ) }
					>
						{ style === 'grid' && <VerticalCardLayout { ...cardProps } /> }
						{ style === 'list' && <HorizontalCardLayout { ...cardProps } /> }
						{ style === 'masonry' && <VerticalCardLayout { ...cardProps } /> }
						{ cardsFocus[ cardIndex ] !== undefined &&
							cardsFocus[ cardIndex ] && (
								<FocusedCardControls
									cardIndex={ cardIndex }
									style={ style }
									numberOfCards={ numberOfCards }
									onMoveLeftOrUp={ () => onCardMoveLeftOrUp( cardIndex ) }
									onMoveRightOrDown={ () => onCardMoveRightOrDown( cardIndex ) }
									onRemove={ () => onCardRemove( cardIndex ) }
								/>
							) }
					</div>
				);
			}
			setCards( items );
		},
		// eslint-disable-next-line react-hooks/exhaustive-deps
		[ cardsFocus, cardsProps, columnSpan, gutter, numberOfCards, style ]
	);

	/**
	 * Handle the card focus event.
	 *
	 * @param {number} cardIndex - Card index.
	 */
	const onCardFocus = cardIndex => {
		const newCardFocus = cardsFocus.map( () => {
			/* istanbul ignore next */
			return false;
		} );
		newCardFocus[ cardIndex ] = true;
		setCardsFocus( newCardFocus );
	};

	/**
	 * Handle the card move left or up event.
	 *
	 * @param {number} cardIndex - Card index.
	 */
	const onCardMoveLeftOrUp = cardIndex => {
		const newCardsProps = [ ...cardsProps ];
		if ( cardIndex > 0 ) {
			const card = newCardsProps[ cardIndex ];
			newCardsProps[ cardIndex ] = newCardsProps[ cardIndex - 1 ];
			newCardsProps[ cardIndex - 1 ] = card;
			setAttributes( {
				cardsProps: newCardsProps,
			} );
			setCardsFocus( [] );
		}
	};

	/**
	 * Handle the card move right or down event.
	 *
	 * @param {number} cardIndex - Card index.
	 */
	const onCardMoveRightOrDown = cardIndex => {
		const newCardsProps = [ ...cardsProps ];
		if ( cardIndex < newCardsProps.length - 1 ) {
			const card = newCardsProps[ cardIndex ];
			newCardsProps[ cardIndex ] = newCardsProps[ cardIndex + 1 ];
			newCardsProps[ cardIndex + 1 ] = card;
			setAttributes( {
				cardsProps: newCardsProps,
			} );
			setCardsFocus( [] );
		}
	};

	/**
	 * Handle the card removal event.
	 *
	 * @param {number} cardIndex - Card index.
	 */
	const onCardRemove = cardIndex => {
		const newCardsProps = [ ...cardsProps ];
		newCardsProps.splice( cardIndex, 1 );
		setAttributes( {
			cardsProps: newCardsProps,
			numberOfCards: numberOfCards - 1,
		} );
		setCardsFocus( [] );
	};

	/**
	 * Handle the click outside a card to remove the focus highlight on all cards.
	 *
	 * @param {Object} event - Click event.
	 */
	/* istanbul ignore next */
	const onClickOutsideCard = event => {
		const cardContainer = event.target.closest( '.card-container' );
		if ( ! cardContainer ) {
			setCardsFocus( [] );
		}
	};

	return (
		<>
			<CardsCollectionInspectorControls { ...inspectorControlsProps } />
			<div className={ className }>
				<Cards
					style={ style }
					gutter={ gutter }
					columns={ columns }
					cards={ cards }
				/>
			</div>
		</>
	);
};

export default Edit;