function rcl_init_table( table_id ) {

    jQuery( '#' + table_id ).on( 'click', '.rcl-table__cell-must-sort', function() {

        jQuery( '#' + table_id ).find( '.rcl-table__cell-must-sort, .rcl-table__cell-sort' ).removeClass( 'rcl-table__cell-current-sort' );

        var sortCell = jQuery( this );

        var sortby = sortCell.data( 'col' );
        var order = sortCell.attr( 'data-order' );

        sortCell.addClass( 'rcl-table__cell-current-sort' );
        jQuery( '#' + table_id ).find( '.rcl-table__cell.col-' + sortby ).addClass( 'rcl-table__cell-current-sort' );

        var list = jQuery( '#' + table_id + ' .rcl-table__row-must-sort' );

        list.sort( function( a, b ) {

            var aVal = jQuery( a ).children( '.rcl-table__cell.col-' + sortby ).data( 'value' );
            var bVal = jQuery( b ).children( '.rcl-table__cell.col-' + sortby ).data( 'value' );

            if ( order === 'desc' )
                return ( aVal < bVal ) - ( aVal > bVal ); //по возрастанию
            else
                return ( aVal > bVal ) - ( aVal < bVal ); //по убыванию
        } );

        sortCell.attr( 'data-order', ( order == 'desc' ? 'asc' : 'desc' ) );

        jQuery( '#' + table_id + ' .rcl-table__row-must-sort' ).remove();

        list.each( function( i, e ) {
            if ( jQuery( '#' + table_id + ' .rcl-table__row-search' ).length ) {
                jQuery( '#' + table_id + ' .rcl-table__row-search' ).after( jQuery( this ) );
            } else {
                jQuery( '#' + table_id + ' .rcl-table__row-header' ).after( jQuery( this ) );
            }
        } );

    } );

}

function rcl_table_search( e, key, submit ) {

    if ( submit ) {

        if ( typeof submit == 'string' ) {

            return window[submit].call( this, e, key, submit );

        } else if ( key == 'Enter' ) {
            jQuery( e ).parents( 'form' ).submit();
        }

        return;

    }

    var table_id = jQuery( e ).parents( '.rcl-table' ).attr( 'id' );

    var inputs = jQuery( e ).parents( '.rcl-table' ).find( '.rcl-table__row-search input' );

    var search = [ ];
    inputs.each( function( i, a ) {

        if ( jQuery( a ).val() !== '' ) {
            search.push( [ jQuery( a ).parent().data( 'rcl-ttitle' ),
                jQuery( a ).val() ] );
        }

    } );

    jQuery( '#' + table_id + ' .rcl-table__row-must-sort' ).show();

    if ( !search.length ) {
        return;
    }

    var list = jQuery( '#' + table_id + ' .rcl-table__row-must-sort' );

    list.each( function( i, r ) {

        var success = true;

        var cells = jQuery( r ).find( '.rcl-table__cell' );

        cells.each( function( x, c ) {

            search.forEach( function( s ) {

                if ( jQuery( c ).data( 'rcl-ttitle' ) == s[0] ) {

                    var value = jQuery( c ).data( 'value' );

                    if ( typeof value == 'number' && jQuery( c ).data( 'value' ) != s[1] ||
                        typeof value == 'string' && value.indexOf( s[1] ) < 0 ) {
                        success = false;
                        return;
                    }

                }

            } );

            if ( !success ) {
                return;
            }

        } );

        if ( !success ) {
            jQuery( r ).hide();
        }

    } );
}