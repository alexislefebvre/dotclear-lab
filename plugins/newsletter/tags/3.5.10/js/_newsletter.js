// paramètres par défaut du formulaire de paramétrage
function pdefaults()
{
	document.settings.feditorname.value = '';
	document.settings.feditoremail.value = '';
} // pdefaults

// action d'édition depuis la liste
function ledit(id)
{
	document.getElementById('listblog').op.value = 'edit';
	document.getElementById('listblog').id.value = id;	
	document.getElementById('listblog').submit();
} // ledit

// import des données
function pimport()
{
	document.impexp.op.value = 'import';
	document.impexp.submit();
} // pimport

// définition d'un état
function lset()
{
    document.getElementById('listblog').op.value = document.getElementById('listblog').fstates.value;
	document.getElementById('listblog').id.value = '';	
	document.getElementById('listblog').submit();
} // lset

// envoi d'un mail
function lsend()
{
    document.getElementById('listblog').op.value = document.getElementById('listblog').fmails.value;
	document.getElementById('listblog').id.value = '';	
	document.getElementById('listblog').submit();
} // lsend

// change le format d'envoi
function lchangemode()
{
	document.getElementById('listblog').op.value = document.getElementById('listblog').fmodes.value;
	document.getElementById('listblog').id.value = '';	
	document.getElementById('listblog').submit();
} // lchangemode

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
} // toggleCheckAll

function checkAll(container_id) 
{ 
	toggleCheckAll(container_id, 1); 
} // checkAll

function uncheckAll(container_id) 
{ 
	toggleCheckAll(container_id, 2); 
} // uncheckAll

function invertcheckAll(container_id) 
{ 
	toggleCheckAll(container_id, 3); 
} // invertcheckAll

function erasingnewsletterConfirm()
{
	if (window.confirm(dotclear.msg.confirm_erasing_task))
	{
		document.erasingnewsletter.submit();
	} 	
} // erasingnewsletterConfirm

function deleteUsersConfirm()
{
	if (window.confirm(dotclear.msg.confirm_delete_user))
	{
		document.getElementById('listblog').submit();
	} 	
} // deleteUsersConfirm

