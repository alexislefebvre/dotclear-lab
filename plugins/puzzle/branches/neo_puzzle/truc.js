lettres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789'
lettres = lettres.split('')

function puzzle_boite ( masque ) {
	
	var masque = masque.split('')
	var tab = []
	var index = 0
	tab[index] = []
	var old = ''
	var stock = 0
	var stockLabel = ''
	var nbcolonne = [{'col':0, 'lig':0}]
	
	for (var key in masque) {
		var cha = masque[key]
		
		if( cha == ']' ) {
			stock --
			if( !stock ) {
				cha = stockLabel
				stockLabel = ''
		}}
		
		if( cha == '[' ) {
			if( stock ) stockLabel += cha
			stock ++
		} else if( !stock ) {
			if( cha == '/' ) {
				tab[++index] = []
				nbcolonne[index] = {'col':0, 'lig':0}
				old = {}
			} else if( cha == '-' || cha == old ) {
				old.mult ++
				nbcolonne[index].col ++
			} else {
				var ocha = {'class':cha, 'mult':1}
				if( cha.length > 1 ) ocha.childs = puzzle_boite(cha)
				tab[index].push(ocha)
				old = ocha
				nbcolonne[index].col ++
			}
		} else
			stockLabel += cha
	}
	
	for (index in tab)
	for (key in tab[index])
		tab[index][key].largeur = 100/nbcolonne[index].col*tab[index][key].mult
	
	return tab
}

function puzzle_boiteview ( layout, lignes ) {
	var str = ''
	for (index in layout) {
		var ligne = layout[index]
		for (key in ligne) {
			var ocha = ligne[key]
			str += '<div class="part '+( ocha.childs? 'nofinal':'final' )+' part-'+ocha.class+'" style="width:'+ocha.largeur+'%;">'
			if( ocha.childs ) {
				str += '<a class="btn del" rel="'+ocha.class+'" href="#">-</a>'
				str += '<span class="tooll"><a class="btn addlp" rel="'+ocha.class+'" href="#">+</a></span>'
				str += '<div class="child">'
				str += '<a class="btn addcp" rel="'+ocha.class+'" href="#">+</a>'
				str += '<a class="btn addcn" rel="'+ocha.class+'" href="#">+</a>'
				str += puzzle_boiteview(ocha.childs, lignes)
				str += '</div>'
				str += '<span class="tooll"><a class="btn addln" rel="'+ocha.class+'" href="#">+</a></span>'
			} else {
				var hauteur = lignes[ocha.class]? lignes[ocha.class]:1
				str += '<div class="child" style="height:'+(hauteur*30+2*(hauteur-1))+'px; ">'
				str += '<a class="btn del" rel="'+ocha.class+'" href="#">-</a>'
				str += '<a class="btn split" rel="'+ocha.class+'" href="#">|</a>'
				str += '<a class="btn splith" rel="'+ocha.class+'" href="#">—</a>'
				str += '<span rel="'+ocha.class+'">'+ocha.class+'</span>'
				str += '</div>'
			}
			str += '</div>'
		}
		str += '<div class="hrpart"/>'
	}
	
	return str
}

function puzzle_lignes () {
	var ligness = $('#lignes').attr('value')
	ligness = ligness.split('|')
	var lignes = {}
	for (key in ligness) {
		var ligne = ligness[key].split(';')
		lignes[ligne[0]] = ligne[1]
	}
	return lignes;
}

function puzzle_clean ( masque ) {
	
	masque = masque.replace( new RegExp("^\\[(.*)\\]$", 'g') ,"$1") // enléve les [] en début et fin de chaine
	masque = masque.replace( new RegExp("\\[{2}([^\\[\\]]*)\\]{2}", 'g') ,"[$1]")	// enléve les doublons [[]]
	masque = masque.replace( new RegExp("//", 'g') ,"/")		// enléve les doublons /
	masque = masque.replace( new RegExp("/$", 'g') ,"")		// enléve les / final
	masque = masque.replace( new RegExp("\\[([^/])\\]", 'g') ,"$1")	// enléve les [A]
	
	return masque;
}

$(function(){
	
	//lignes = puzzle_lignes()

	//alert(JSON.stringify( lignes ))
	
	$('#view').click (function(){
		
		$('#phrase').attr('value', puzzle_clean($('#phrase').attr('value')))
		var layout = {'class':$('#phrase').attr('value'), 'mult':1, 'largeur':100}
		layout.childs = puzzle_boite ( $('#phrase').attr('value') )
		
		$('#base')
			.empty()
			.append(puzzle_boiteview ( [[layout]], puzzle_lignes() ))
		
		$('.final').mouseover(function(){
				$('.over').removeClass('over')
				$(this).parents('.nofinal:first').addClass('over')
			})
		$('.over').mouseout(function(){ $(this).removeClass('over') });


		
		
		$('.addlp').click(function () {
			var i = 0; while( $("span[rel="+lettres[i]+"]").length ) i++
			
			cha = $(this).attr('rel')
			cha = cha.replace( new RegExp('(\\[|\\])', 'g') ,"\\$1")
			
			masque = $('#phrase').attr('value')
			masque = masque.replace( new RegExp("("+cha+")") ,'['+lettres[i]+'/$1]')
			
			$('#phrase').attr('value', masque)
			$('#view').click()
		})
		
		$('.addln').click(function () {
			var i = 0; while( $("span[rel="+lettres[i]+"]").length ) i++
			
			cha = $(this).attr('rel')
			cha = cha.replace( new RegExp('(\\[|\\])', 'g') ,"\\$1")
			
			masque = $('#phrase').attr('value')
			masque = masque.replace( new RegExp("("+cha+")") ,'[$1/'+lettres[i]+']')
			
			$('#phrase').attr('value', masque)
			$('#view').click()
		})
			
		$('.addcp').click(function () {
			var i = 0; while( $("span[rel="+lettres[i]+"]").length ) i++
			
			cha = $(this).attr('rel')
			masque = $('#phrase').attr('value')
			
			chatest = cha.replace( new RegExp('\\[.*\\]', 'g') ,"X")
			cha = cha.replace( new RegExp('(\\[|\\])', 'g') ,"\\$1")
			
			if( new RegExp("/").test(chatest) )
				masque = masque.replace( new RegExp("("+cha+")") ,lettres[i]+'[$1]')
			else	masque = masque.replace( new RegExp("("+cha+")") ,lettres[i]+'$1')
			
			$('#phrase').attr('value', masque)
			$('#view').click()
		})
			
		$('.addcn').click(function () {
			var i = 0; while( $("span[rel="+lettres[i]+"]").length ) i++
			
			cha = $(this).attr('rel')
			masque = $('#phrase').attr('value')
			
			chatest = cha.replace( new RegExp('\\[.*\\]', 'g') ,"X")
			cha = cha.replace( new RegExp('(\\[|\\])', 'g') ,"\\$1")
			
			if( new RegExp("/").test(chatest) )
				masque = masque.replace( new RegExp("("+cha+")") ,'[$1]'+lettres[i])
			else	masque = masque.replace( new RegExp("("+cha+")") ,'$1'+lettres[i])
			
			$('#phrase').attr('value', masque)
			$('#view').click()
		})
			
		$('.split').click(function () {
			var i = 0; while( $("span[rel="+lettres[i]+"]").length ) i++
			
			cha = $(this).attr('rel')
			masque = $('#phrase').attr('value')
			masque = masque.split(cha).join(cha+lettres[i])
			
			$('#phrase').attr('value', masque)
			$('#view').click()
		})
			
		$('.splith').click(function () {
			var i = 0; while( $("span[rel="+lettres[i]+"]").length ) i++
			
			cha = $(this).attr('rel')
			masque = $('#phrase').attr('value')
			masque = masque.split(cha).join('['+cha+'/'+lettres[i]+']')
			
			$('#phrase').attr('value', masque)
			$('#view').click()
		})
			
		$('.del')
			.click(function () {
				
				cha = $(this).attr('rel')
				cha = cha.replace( new RegExp('(\\[|\\])', 'g') ,"\\$1")
				
				masque = $('#phrase').attr('value')
				masque = masque.replace( new RegExp("\\[?"+cha+"\\]?-*") ,"")
				
				$('#phrase').attr('value', masque)
				$('#view').click()
			})
	})
	
	$('#view').click()


}) 



