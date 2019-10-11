function getStates(region) {
   var regionColors = ['#5c7981', '#643c18', '#b2721a', '#dbd543', '#679a61', '#e28e45', '#508dac', '#a48f70',
       '#9dcbdb', '#d99b9c', '#dbd543', '#b2721a', '#9dcbdb'];
   var regionStates = [
      ['CT', 'MA', 'ME', 'NH', 'RI', 'VT', ],
      ['NJ', 'NY', ],
      ['DE', 'MD', 'PA', 'VA', 'WV', ],
      ['AL', 'FL', 'GA', 'KY', 'MS', 'NC', 'SC', 'TN', ],
      ['IL', 'IN', 'MI', 'MN', 'OH', 'WI', ],
      ['AR', 'LA', 'TX', 'NM', 'OK', ],
      ['IA', 'KS', 'MO', 'NE', ],
      ['CO', 'MT', 'ND', 'SD', 'UT', 'WY', ],
      ['AZ', 'CA', 'HI', 'NV', ],
      ['AK', 'ID', 'OR', 'WA', ],
      ['GA'],
      ['MD'],
      ['CA']
   ];
   
   var style = {};
   
   // Case 1: region is a single state.
   if (region == 55) { // CA
      style['CA'] = {fill: '#9dcbdb'};
   } else if (region == 13) { // DC
      style['MD'] = {fill: '#b2721a'};
   } else if (region == 12) { // GA
      style['GA'] = {fill: '#dbd543'};
   } else if (region == 0) { // Case 2: region is national.
      var num_hhs_regions = 10;
      for(var i = 0; i < num_hhs_regions; i++) {
         for(var j = 0; j < regionStates[i].length; j++) {
            style[regionStates[i][j]] = {fill: regionColors[i]};
         }
      }
   } else { // Case 3: region is a HHS region that contains multiple states.
      var states = regionStates[region - 1]; // here, hhs1 corresponds to region 1, which is the 0th index in regionStates.
      var region_color = regionColors[region - 1];
      for(var i = 0; i < states.length; i++) {
         style[states[i]] = {fill: region_color};
      }
   }
   
   return style;
}
