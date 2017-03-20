# Agora

### About this plugin
Agora is where each moderator can create a �Agora� to start a new discussion/debate that is open to all the users. Once a room is created, only the administrator can 

delete the room. Each room is represented by a "tile" that has three characteristics (colour, height and width) that are related to the discussion in it (more details below).

The user that created an Agora can also add a set of recommended datasets, that are easily accessible by the participants in that Agora (via the Controllet). To 
add recommended datasets, click on the list to the left (to show "My Agora") and select the room).

The tile of each Agora shows a synthetic visualisation of its discussion by using colour, height and width as visual metaphors:

* The **colour** of the tile goes from blue (only few visits by users, i.e. a "cold" topic) to red (many visits by users, i.e. a "hot" topic). The colour is picked from a 
  blue-to-red gradient proportionally to the number of visits.
* The **width** of a tile also can have only two values, i.e. narrow (strict) or wide (large) depending on the number of distinct datalets that are used.
* To set the **height**, the median of the number of comments for all the rooms is calculated, and the rooms about the median are "tall" and those below are "short". 
  The same happes for the width with respect to the number of distinct datalets.

Rooms are split vertically, on the left side there is a *threaded chat* (enabled for the use of open data) meanwhile on the right there is an area dedicated to *Graphs*.

* The *threaded chat* has a maximum of three nested level. The number of nested levels is limited to avoid straying too far from the main topic and make less readable the conversation. 
  Every time you add a new post in the chat, you can express you opinion by selecting from the Opinion Button
* There are four graph:
  - *Comments Graph*: tree of the comments, where each node represents a comment and the edges the parent-child relations. The dimension of a node is directly proportional 
     to the numbers of nested comments, rooted in the node. The larger the node, the more comments are present below it in the discussion. The color can be green, red or blue, 
     depending on the opinion. If you bring the mouse over a node (no click) you can read the comment of the node, and if you click the chat on the left will move and highlight 
     the corresponding comment.
  - *Datalets Graph*: graph of datalets, where each node represents a datalet, and edges join datalet that uses the same dataset (but possibly with a different visualization). 
     The dimension of a node is directly proportional to the numbers of nested comments, rooted in the node. The larger the node, the more comments are present below it in the 
     discussion. If you bring the mouse over a node (no click) you can read the comment of the node and if you click the chat on the left will move and highlight the 
     corresponding comment.
  - *Users Graph*: graph of users, where each node represents a user that is participating in the discussion, and an edge joins to users that interacted (one answered a comment 
     of the other). The thickness of the edge is directly proportional to the number of interactions. If you bring the mouse over a node (no click) you can read user's first 
     comment.
  - *Opinions Graph*: comments graph with the all the green (agree) adjacent comments are emphasized and grouped together. In this way, all the comments that seem to share a 
     position are highlighted.
     
In the *Agora admin panel* the admin user can delete or modify the rooms.     
     
### Installation guide 

To install *Agora* plugin:

* Clone this project by following the github instruction on *SPOD_INSTALLATION_DIR/ow_plugins*
* Install the plugin on SPOD by *admin plugins panel*

     
