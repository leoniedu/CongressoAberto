function setupSniplets ()
{
  jQuery('.sniplet-edit').click (function (item)
  {
    jQuery(this).parent ().find ('.sniplet-loading').show ();
    jQuery(this).parent ().load (this.href);
    return false;
  });
  
  jQuery('.sniplet-delete').click (function (item)
  {
    jQuery(this).parent ().find ('.sniplet-loading').show ();
    jQuery(this).parent ().load (this.href, {}, function()
      {
        jQuery(this).parent ().remove ();
        
        if (jQuery('#sniplets li').length > 1)
          jQuery('#sort_sniplet').show ();
        else
          jQuery('#sort_sniplet').hide ();
      });
    return false;
  });
  
  jQuery('.sniplet-toggle').click (function (item)
  {
    jQuery(this).parent ().find ('.sniplet-loading').show ();
    jQuery(this).parent ().parent ().load (this.href, {}, function ()
      {
        if (jQuery(this).hasClass ('enabled'))
        {
          jQuery(this).removeClass ('enabled');
          jQuery(this).addClass ('disabled');
        }
        else
        {
          jQuery(this).removeClass ('disabled');
          jQuery(this).addClass ('enabled');
        }
        
        setupSniplets ();
      });
    return false;
  });
}

function setupEditSniplet (base, snipid, nonce)
{
  jQuery('#snip_' + snipid + ' .sniplet-cancel').click (function (item)
	{
    jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
		jQuery('#snip_' + snipid).load (base + '?cmd=show&id=' + snipid, {}, function () { setupSniplets (); });
		return false;
	});

  jQuery('#snip_' + snipid + ' .sniplet-save').click (function (item)
	{
    jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
		jQuery('#snip_' + snipid).load (base + '?cmd=save&id=' + snipid + '&_ajax_nonce=' + nonce,
		  {
		    name:     jQuery('#snip_name_' + snipid).val(),
		    contents: jQuery('#snip_contents_' + snipid).val(),
		    template: jQuery('#snip_template_' + snipid).val(),
		    post_id:  jQuery('#snip_post_' + snipid).val()
		  }, function () { setupSniplets (); });
		return false;
	});
	
	jQuery('#snip_' + snipid + ' .sniplet-advanced').click (function (item)
	{
	  var option = jQuery('#advanced_' + snipid).val ();
	  if (option == 'duplicate')
	    dup_snip (snipid, nonce);
    else if (option == 'module')
      add_snip_function (snipid, nonce);
    else if (option == 'placement')
      add_snip_placement (snipid, nonce);
	    
		return false;
	});
  
	
	setupEditFunctions (snipid);
	setupEditPlacements (snipid);
}

function setupEditPlacements (snipid)
{
	jQuery('#snip_' + snipid + ' .sniplet-delete-placement').click (function (item)
	{
    jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
	  jQuery('#placements_' + snipid).load (this.href, {}, function () { setupEditPlacements(); jQuery('#snip_' + snipid + ' .sniplet-loading').hide () });
	  return false;
	});

	jQuery('#snip_' + snipid + ' .sniplet-edit-placement').click (function (item)
	{
    jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
	  jQuery(this).parent ().load (this.href, {}, function () { jQuery('#snip_' + snipid + ' .sniplet-loading').hide () });
	  return false;
	});
}

function setupEditFunctions (snipid)
{
	jQuery('#snip_' + snipid + ' .sniplet-delete-function').click (function (item)
	{
    jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
	  jQuery('#functions_' + snipid).load (this.href, {}, function () { setupEditFunctions(); jQuery('#snip_' + snipid + ' .sniplet-loading').hide () });
	  return false;
	});

	jQuery('#snip_' + snipid + ' .sniplet-edit-function').click (function (item)
	{
    jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
	  jQuery(this).parent ().load (this.href, {}, function () { jQuery('#snip_' + snipid + ' .sniplet-loading').hide () });
	  return false;
	});
}

function dup_snip (snipid, nonce)
{
  jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
  jQuery.post (wp_snip_base + 'cmd=dup_sniplet&id=' + snipid + '&_ajax_nonce=' + nonce, {}, function(response)
    {
      jQuery('#snip_' + snipid + ' .sniplet-loading').hide ();
      jQuery('#sniplets').append (response);
    });

  return false;
}

function add_snip_placement (snipid, nonce)
{
  jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
  jQuery('#placements_' + snipid).load (wp_snip_base + 'cmd=add_placement&id=' + snipid + '&_ajax_nonce=' + nonce, {}, function ()
    {
      jQuery('#snip_' + snipid + ' .sniplet-loading').hide ();
      setupEditPlacements (snipid);
      jQuery('#placements_' + snipid).show ();
    });
  
  return false;
}

function add_snip_function (snipid, nonce)
{
  jQuery('#snip_' + snipid + ' .sniplet-loading').show ();
  jQuery('#functions_' + snipid).load (wp_snip_base + 'cmd=add_function&id=' + snipid + '&_ajax_nonce=' + nonce, {}, function ()
    {
      jQuery('#snip_' + snipid + ' .sniplet-loading').hide ();
      setupEditFunctions (snipid);
      jQuery('#functions_' + snipid).show ();
    });
  
  return false;
}

function toggle_sort (onoff,nonce)
{
  if (onoff == true)
  {
    jQuery('#toggle_sort_on').hide ();
    jQuery('#toggle_sort_off').show ();
    jQuery('#sniplets li').addClass ('sortable');
    jQuery('#sniplets').sortable ();
  }
	else
	{
    jQuery('#toggle_sort_on').show ();
    jQuery('#toggle_sort_off').hide ();
    jQuery('#sniplets li').removeClass ('sortable');
	  
	  save_sniplet_order (0, nonce);
  }
  return false;
}

function save_sniplet_order (id, nonce)
{
  jQuery('#sniplet-loading').show ();
  
  jQuery.post (wp_snip_base + 'cmd=save_order&id=' + id + '&_ajax_nonce=' + nonce, jQuery('#sniplets').sortable('serialize'), function()
    {
      jQuery('#sniplet-loading').hide ();
      jQuery('#sniplets').sortable('disable');
    });
  return false;
}

function add_page_sniplet (id, nonce)
{
  if (jQuery('#sniplet_name').val ())
  {
    jQuery('#sniplet_loading').show ();
    
    jQuery.post(wp_snip_base + 'cmd=add_sniplet&id=' + id + '&_ajax_nonce=' + nonce,
      {
        name:      jQuery('#sniplet_name').val (),
        contents:  jQuery('#sniplet_content').val (),
        placement: jQuery('#sniplet_place').val (),
        template:  jQuery('#sniplet_template').val ()
      },
      function (response)
      {
        jQuery('#sniplet_loading').hide ();
        jQuery('#sniplet_name').val ('');
        jQuery('#sniplet_content').val ('');
        
        jQuery('#sniplets').append (response);
        
        if (jQuery('#sniplets li').length > 1)
          jQuery('#sort_sniplet').show ();
        else
          jQuery('#sort_sniplet').hide ();
      });
  }

  return false;
}


addLoadEvent(function()
{
	var toolbar = document.getElementById ("ed_toolbar");
	if (toolbar)
	{
    edButtons[edButtons.length] = new edButton('ed_sniplet' ,'sniplet','[sniplet name]','','snip');
    
    var but = toolbar.lastChild;
		while (but.nodeType != 1)
			but = but.previousSibling;

		but = but.cloneNode (true);
		toolbar.appendChild(but);
		but.value = 'sniplet';
		but.title = edButtons.length - 1;
		but.onclick = function () {edInsertTag(edCanvas, parseInt(this.title));}
		but.id = "ed_sniplet";
  }
});
