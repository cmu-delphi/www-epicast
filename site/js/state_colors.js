function getStates(region) {
   var regionColors = ['#5c7981', '#643c18', '#b2721a', '#dbd543', '#679a61', '#e28e45', '#508dac', '#a48f70', '#9dcbdb', '#d99b9c', '#b2721a', '#dbd543', '#b2721a'];
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
      ['PA'],
      ['GA'],
      ['MD']
   ];
   var style = {};
   for(var i = 0; i < 13; i++) {
      if(region === 0 || region === i + 1) {
         for(var j = 0; j < regionStates[i].length; j++) {
            style[regionStates[i][j]] = {fill: regionColors[i]};
         }
      }
   }
   return style;
}
