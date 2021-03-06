// paramètres par défaut du formulaire de paramétrage
function pdefaults()
{
	document.state.active.checked = false;
	document.settings.feditorname.value = '';
	document.settings.feditoremail.value = '';
	document.settings.fmaxposts.value = '7';
	document.settings.fmaxinpast.value = '14';

}

// action d'édition depuis la liste
function ledit(id)
{
	document.listblog.op.value = 'edit';
	document.listblog.id.value = id;	
	document.listblog.submit();
}

// import des données
function pimport()
{
	document.impexp.op.value = 'import';
	document.impexp.submit();
}

// définition d'un état
function lset()
{
    document.listblog.op.value = document.listblog.fstates.value;
	document.listblog.id.value = '';	
	document.listblog.submit();
}

// envoi d'un mail
function lsend()
{
    document.listblog.op.value = document.listblog.fmails.value;
	document.listblog.id.value = '';	
	document.listblog.submit();
}

// change le format d'envoi
function lchangemode()
{
	document.listblog.op.value = document.listblog.fmodes.value;
	document.listblog.id.value = '';	
	document.listblog.submit();
}

// sélection des lignes
function toggleCheckAll(container_id, what)
{
	var rows = document.getElementById(container_id).getElementsByTagName('tr');
	var checkbox;

	for (var i=0; i < rows.length; i++)
	{
		checkbox = rows[i].getElementsByTagName("input")[0];
		
		if (checkbox && checkbox.type == "checkbox")
		{
			switch (what)
			{
				case 1:
					checkbox.checked = true;
					break;

				case 2:
					checkbox.checked = false;
					break;

				case 3:
					checkbox.checked = !checkbox.checked;
					break;
			}
		}
	}

}
function checkAll(container_id) { toggleCheckAll(container_id, 1); }
function uncheckAll(container_id) { toggleCheckAll(container_id, 2); }
function invertcheckAll(container_id) { toggleCheckAll(container_id, 3); }

function erasingnewsletterConfirm()
{
	if (window.confirm(dotclear.msg.confirm_erasing_task))
	{
		document.erasingnewsletter.submit();
	} 	
}

function deleteUsersConfirm()
{
	if (window.confirm(dotclear.msg.confirm_delete_user))
	{
		document.listblog.submit();
	} 	
}
