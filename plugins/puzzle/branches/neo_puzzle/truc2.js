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
		
		if( ligne.length>1 ) str += '<div class="part part-hori">'
		
		for (key in ligne) {
			var ocha = ligne[key]
			str += '<div class="part'+( ocha.childs? ' part-vert':' final' )+'" style="width:'+ocha.largeur+'%;">'
				if( !ocha.childs ) {
					str += '<div class="child" style="height:40px;">'
					//str += ocha.class
					str += '</div>'
				} else
					str += puzzle_boiteview(ocha.childs, lignes)
			str += '</div>'
		}
		
		if( ligne.length>1 ) str += '</div>'
		str += '<div class="hrpart"/>'
		
	}
	
	return str
}

function puzzle_btnview ( layout, lignes ) {
	var str = ''
	for (index in layout) {
		var ligne = layout[index]
		
		if( ligne.length>1 ) {
			str += '<div class="part part-hori">'
			str += '<a class="btn addcp" rel="'+ocha.class+'" href="#"></a>'
			str += '<a class="btn addcn" rel="'+ocha.class+'" href="#"></a>'
		}
		
		for (key in ligne) {
			var ocha = ligne[key]
			str += '<div class="part'+( ocha.childs? ' part-vert':' final' )+'" style="width:'+ocha.largeur+'%;">'
				if( !ocha.childs ) {
					var hauteur = lignes[ocha.class]? lignes[ocha.class]:1
					str += '<div class="child" style="height:'+(hauteur*30+2*(hauteur-1))+'px;">'
					str += '</div>'
				} else {
					str += '<div style="width: 100%; position: absolute; top: 0pt;"><div style="margin: 1px;"><a class="btn addlp" rel="'+ocha.class+'" href="#"></a></div></div>'
					str += '<div style="width: 100%; position: absolute; bottom: 0pt;"><div style="margin: 1px;"><a class="btn addln" rel="'+ocha.class+'" href="#"></a></div></div>'
					str += puzzle_btnview(ocha.childs, lignes)
				}
			str += '</div>'
		}
		
		if( ligne.length>1 ) {
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
	
	//alert(JSON.stringify( lignes ))
	
	$('#view').click (function(){
		
		$('#phrase').attr('value', puzzle_clean($('#phrase').attr('value')))
		var layout = {'class':$('#phrase').attr('value'), 'mult':1, 'largeur':100}
		layout.childs = puzzle_boite ( $('#phrase').attr('value') )
		
		$('#base')
			.empty()
			.append(puzzle_boiteview ( [[layout]], puzzle_lignes() ))
		
		$('.child').click (fonction(){
			
		})
		
	})
	
	$('#view').click()


}) 



