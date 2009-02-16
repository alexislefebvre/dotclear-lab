// param�tres par d�faut du formulaire de param�trage
function pdefaults()
{
	document.state.active.checked = false;
	document.settings.feditorname.value = '';
	document.settings.feditoremail.value = '';
	document.settings.fmaxposts.value = '7';
	document.settings.fmaxinpast.value = '14';
	document.settings.fmode['text'].checked = true;
	document.settings.fupdated['no'].checked = true;
}

// action d'�dition depuis la liste
function ledit(id)
{
	document.listblog.op.value = 'edit';
	document.listblog.id.value = id;	
	document.listblog.submit();
}

// import des donn�es
function pimport()
{
	document.impexp.op.value = 'import';
	document.impexp.submit();
}

// d�finition d'un �tat
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

// s�lection des lignes
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

