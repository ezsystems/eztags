{literal}
<script type="text/javascript">
function TagsStructureMenu( path, persistent, attr_id )
{
    this.cookieName     = "tagsStructureMenu";
    this.cookieValidity = 3650; // days
    this.useCookie      = persistent;
    this.cookie         = this.useCookie ? _getCookie( this.cookieName ) : '';
    this.open           = ( this.cookie ) ? this.cookie.split( '/' ): [];
    this.autoOpenPath   = path;
    this.attribute_id   = attr_id;
{/literal}

{default current_user=fetch('user','current_user')}
    this.perm = "{concat($current_user.role_id_list|implode(','),'|',$current_user.limited_assignment_value_list|implode(','))|md5}";
{/default}

    this.expiry = "{fetch('content','content_tree_menu_expiry')}";

    this.hideTagID = jQuery('#hide_tag_id_' + this.attribute_id).val();

    this.showTips       = {if ezini('TreeMenu','ToolTips','eztags.ini')|eq('enabled')}true{else}false{/if};
    this.autoOpen       = false;

{literal}
    this.updateCookie = function()
    {
        if ( !this.useCookie )
            return;
        this.cookie = this.open.join('/');
        expireDate  = new Date();
        expireDate.setTime( expireDate.getTime() + this.cookieValidity * 86400000 );
        _setCookie( this.cookieName, this.cookie, expireDate );
    };

    // cookie functions
    function _setCookie( name, value, expires, path )
    {
        document.cookie = name + '=' + escape(value) + ( expires ? '; expires=' + expires.toUTCString(): '' ) + '; path='+ (path ? path : '/');
    }

    function _getCookie( name )
    {
        var n = name + '=', c = document.cookie, start = c.indexOf( n ), end = c.indexOf( ";", start );
        if ( start !== -1 )
        {
            return unescape( c.substring( start + n.length, ( end === -1 ? c.length : end ) ) );
        }
        return null;
    }

    function _delCookie( name )
    {
        _setCookie( name, '', ( new Date() - 86400000 ) );
    }

    this.setOpen = function( tagID )
    {
        if ( jQuery.inArray( '' + tagID, this.open ) !== -1 )
        {
            return;
        }
        this.open[this.open.length] = tagID;
        this.updateCookie();
    };

    this.setClosed = function( tagID )
    {
        var openIndex = jQuery.inArray( '' + tagID, this.open );
        if ( openIndex !== -1 )
        {
            this.open.splice( openIndex, 1 );
            this.updateCookie();
        }
    };

    this.generateEntry = function( item, lastli, isRootTag )
    {
        var liclass = '';
        if ( lastli )
        {
            liclass += ' lastli';
        }
        if ( path && ( path[path.length-1] == item.id || ( !item.has_children && jQuery.inArray( item.id, path ) !== -1 ) ) )
        {
            liclass += ' currentnode';
        }
        if (item.id == this.hideTagID) {liclass += ' disabled';}
        var html = '<li id="n-' + this.attribute_id + '-' + item.id + '"' + ( ( liclass )? ' class="' + liclass + '"': '' ) + '>';
        if ( item.has_children && !isRootTag )
        {
            html += '<a class="openclose-open" id="a-' + this.attribute_id + '-'
                + item.id
                + '" href="#" onclick="this.blur(); return treeMenu_' + this.attribute_id + '.load( this, '
                + item.id
                + ', '
                + item.modified
                +' )"><\/a>';
        }

        html += '<a class="nodeicon" href="#" rel="' + item.id + '"><img src="' + item.icon + '" alt="" title="Icon" /><\/a>&nbsp;<a class="image-text" href="#" rel="' + item.id + '"';

        if ( this.showTips )
        {
{/literal}
            html += ' title="{"Tag ID"|i18n('extension/eztags/tags/treemenu')|wash|wash(javascript)}: '
                + item.id
                + ', {"Parent tag ID"|i18n('extension/eztags/tags/treemenu')|wash|wash(javascript)}: '
                + item.parent_id
                + '"';
{literal}
        }

        html += '><span class="node-name-normal">'
            + item.keyword;

        if(item.synonyms_count > 0)
        {
            html += ' (+' + item.synonyms_count + ')';
        }

        html += '<\/span>';

        html += '<\/a>';
        html += '<div id="c-' + this.attribute_id + '-' + item.id + '"><\/div>';
        html += '<\/li>';

        return html;
    };

    this.load = function( aElement, tagID, modifiedSubnode )
    {
        var divElement = document.getElementById('c-' + this.attribute_id + '-' + tagID);

        if ( !divElement )
        {
            return false;
        }

        if ( divElement.className == 'hidden' )
        {
            divElement.className = 'loaded';
            if ( aElement )
            {
                aElement.className = 'openclose-close';
            }

            this.setOpen( tagID );

            return false;
        }

        if ( divElement.className == 'loaded' )
        {
            divElement.className = 'hidden';
            if ( aElement )
            {
                aElement.className = 'openclose-open';
            }

            this.setClosed( tagID );

            return false;
        }

        if ( divElement.className == 'busy' )
        {
            return false;
        }

{/literal}
        var url = "{"tags/treemenu"|ezurl(no)}/" + tagID
            + "/" + modifiedSubnode
            + "/" + this.expiry
            + "/" + this.perm;
{literal}

        divElement.className = 'busy';
        if ( aElement )
        {
            aElement.className = "openclose-busy";
        }

        var thisThis = this;

        var request = jQuery.ajax({
            'url': url,
            'dataType': 'json',
            'success': function( data, textStatus )
            {
                var html = '<ul>';
                // Generate html content
                for ( var i = 0, l = data.children_count; i < l; i++ )
                {
                    html += thisThis.generateEntry( data.children[i], i == l - 1, false );
                }
                html += '<\/ul>';

                divElement.innerHTML += html;
                divElement.className = 'loaded';
                if ( aElement )
                {
                    aElement.className = 'openclose-close';
                }

                thisThis.setOpen( tagID );
                thisThis.openUnder( tagID );

                return;
            },
            'error': function( xhr, textStatus, errorThrown )
            {
                if ( aElement )
                {
                    aElement.className = 'openclose-error';

                    switch( xhr.status )
                    {
                        case 403:
                        {
{/literal}
                            aElement.title = '{"Dynamic tree not allowed for this siteaccess"|i18n('extension/eztags/tags/treemenu')|wash(javascript)}';
{literal}
                        } break;

                        case 404:
                        {
{/literal}
                            aElement.title = '{"Tag does not exist"|i18n('extension/eztags/tags/treemenu')|wash(javascript)}';
{literal}
                        } break;

                        case 500:
                        {
{/literal}
                            aElement.title = '{"Internal error"|i18n('extension/eztags/tags/treemenu')|wash(javascript)}';
{literal}
                        } break;
                    }
                    aElement.onclick = function()
                    {
                        return false;
                    }
                }
            }
        });

        return false;
    };

    this.openUnder = function( parentTagID )
    {
        var divElement = document.getElementById( 'c-' + this.attribute_id + '-' + parentTagID );
        if ( !divElement )
        {
            return;
        }

        var ul = divElement.getElementsByTagName( 'ul' )[0];
        if ( !ul )
        {
            return;
        }

        var children = ul.childNodes;
        for ( var i = 0; i < children.length; i++ )
        {
            var liCandidate = children[i];
            if ( liCandidate.nodeType == 1 && liCandidate.id )
            {
                var tagID = liCandidate.id.substr( 3 + this.attribute_id.length ), openIndex = jQuery.inArray( tagID, this.autoOpenPath );
                if ( this.autoOpen && openIndex !== -1 )
                {
                    this.autoOpenPath.splice( openIndex, 1 );
                    this.setOpen( tagID );
                }
                if ( jQuery.inArray( tagID, this.open ) !== -1 )
                {
                    var aElement = document.getElementById( 'a-' + this.attribute_id + '-' + tagID );
                    if ( aElement )
                    {
                        aElement.onclick();
                    }
                }
            }
        }
    };

    this.collapse = function( parentTagID )
    {
        var divElement = document.getElementById( 'c-' + this.attribute_id + '-' + parentTagID );
        if ( !divElement )
        {
            return;
        }

        var aElements = divElement.getElementsByTagName( 'a' );
        for ( var index in aElements )
        {
            var aElement = aElements[index];
            if ( aElement.className == 'openclose-close' )
            {
                var tagID        = aElement.id.substr( 3 + this.attribute_id.length );
                var subdivElement = document.getElementById( 'c-' + this.attribute_id + '-' + tagID );
                if ( subdivElement )
                {
                    subdivElement.className = 'hidden';
                }
                aElement.className = 'openclose-open';
                this.setClosed( tagID );
            }
        }

        var aElement = document.getElementById( 'a-' + this.attribute_id + '-' + parentTagID );
        if ( aElement )
        {
            divElement.className = 'hidden';
            aElement.className   = 'openclose-open';
            this.setClosed( parentTagID );
        }
    };
}
</script>
{/literal}