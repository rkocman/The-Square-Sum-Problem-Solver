The Square-Sum Problem Solver
========
These scripts try to solve The Square-Sum Problem for all runs 1-*.  
This problem was introduced by Matt Parker in the Numberphile videos:  
[The Square-Sum Problem - Numberphile](https://youtu.be/G1m7goLCJDY)  
[The Square-Sum Problem (extra footage) - Numberphile](https://youtu.be/7_ph5djCCnM)

**In short:**  
The scripts check whether runs of numbers can be organized into sequences where every consecutive pair of numbers adds to a square.

**Environment:** PHP 7, Python 3

This is a solution without any mathematical libraries.

### The Square-Sum Problem Solver (All Paths Version)
This version tries to find all possible answers for every tested run.  
To achieve that it tracks all possible paths in a graph for a given run.  
The following runs are always based on the results of the previous ones.

**Results:**  
Due to the explosion of all possible paths in a graph around the run 1-27, it is very hard to obtain any further answers for longer runs.

### The Square-Sum Problem Solver (Backtracking Version)
This version uses backtracking to find an answer for a tested run.  
Single runs are independent of each other (unlike in the all paths version).  
However, the script uses several simple heuristics to utilize the previous result:
- It tries to trivially expand the previous path with a new vertex.
- It fast-forwards backtracking to the state where a new vertex can occur. 

**Results:**  
It is relatively easy to get answers up to the run 1-75.
But unless some heuristic hits, the follow-up results are very slow.


## Notes
With the use of mathematical libraries, and thus more efficient techniques for finding Hamiltonian paths, it is possible to solve runs up to at least 1-299.
(Or even far beyond -- see the [Wolfram solution](http://community.wolfram.com/groups/-/m/t/1264240) with runs around 1-2700.)
In this sense, the presented solvers are kind of Parker Square solutions.


## License
MIT License