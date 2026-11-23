# QueueLess — M/M/c & Erlang-C Waiting Time Prediction Algorithm

---

## 1. Mathematical Foundation: Multi-Server M/M/c Queue

In classical queuing theory, an **M/M/c queue** models a system where:
- **M (Markovian Arrivals)**: Arrival process follows a Poisson distribution with mean arrival rate $\lambda$ (arrivals per minute).
- **M (Markovian Service Times)**: Service durations are exponentially distributed with mean service rate $\mu$ per counter ($\mu = 1 / \bar{S}$, where $\bar{S}$ is average service handling time in minutes).
- **c (Parallel Servers)**: $c$ identical parallel counters serve students from a single first-come, first-served (FCFS) queue.

---

## 2. Key Formulations

### 2.1 Offered Load & Traffic Intensity
The offered load $a$ (in Erlangs) is:
$$a = \frac{\lambda}{\mu} = \lambda \cdot \bar{S}$$

The system server utilization factor $\rho$ across $c$ counters is:
$$\rho = \frac{a}{c} = \frac{\lambda}{c \mu}$$

*Condition for Queue Stability*:
The queue remains stable only if $\rho < 1$ ($\lambda < c \mu$). If $\rho \ge 1$, the arrival rate exceeds counter capacity and the queue grows unbounded without intervention.

---

### 2.2 Erlang-C Formula (Probability of Waiting)
The probability $P_0$ that the system is entirely idle (zero students present):
$$P_0 = \left[ \sum_{k=0}^{c-1} \frac{a^k}{k!} + \frac{a^c}{c!} \left( \frac{1}{1 - \rho} \right) \right]^{-1}$$

The **Erlang-C formula** gives the probability $C(c, a)$ that an arriving student finds all $c$ counters occupied and must wait:
$$C(c, a) = P(\text{Wait} > 0) = \frac{\frac{a^c}{c!} \left(\frac{1}{1 - \rho}\right)}{\sum_{k=0}^{c-1} \frac{a^k}{k!} + \frac{a^c}{c!} \left(\frac{1}{1 - \rho}\right)} = P_0 \cdot \frac{a^c}{c! (1 - \rho)}$$

---

### 2.3 Expected Queue Waiting Time ($W_q$)
The theoretical expected waiting time in queue before service begins:
$$W_q = \frac{C(c, a)}{c \mu - \lambda} = \frac{C(c, a)}{c \mu (1 - \rho)}$$

---

## 3. Practical Production Hybrid Algorithm

In high-density campus environments (e.g. Accounts, Registrar, or Clinic), purely theoretical stochastic assumptions can deviate due to burst arrivals between classes and varying document complexity.

Therefore, QueueLess implements a **Practical Production Hybrid Estimator**:

```
Estimated Wait = f(Theoretical M/M/c, Rolling Mean Service Time, Position, Active Counters)
```

### Steps:
1. **Dynamic Rolling Service Time ($\mu_{\text{rolling}}$)**:
   Calculated from the last $N=30$ completed `service_sessions` for that service:
   $$\bar{S}_{\text{rolling}} = \frac{1}{N} \sum_{i=1}^{N} \text{duration}_i$$
   If insufficient samples exist ($N < 5$), fallback to the department's configured baseline `avg_handling_time_min`.

2. **Active Counter Discovery ($c_{\text{active}}$)**:
   Query active counters currently staffed:
   $$c_{\text{active}} = \max(1, \text{COUNT}(\text{counters where status} \in \{\text{'AVAILABLE'}, \text{'BUSY'}\}))$$

3. **Current Position Delay ($k = \text{people ahead}$)**:
   Deterministic component for position $k$:
   $$T_{\text{deterministic}} = \left\lceil \frac{k}{c_{\text{active}}} \right\rceil \times \bar{S}_{\text{rolling}}$$

4. **Stochastic Blending**:
   When queue is deep, stochastic queue variance increases:
   $$\text{Estimated Wait (minutes)} = \text{round}\left( 0.70 \cdot T_{\text{deterministic}} + 0.30 \cdot W_q \right)$$

---

## 4. Intelligent Capacity Recommendation Engine

The system continuously evaluates utilization $\rho$:
- **Normal Operations**: $\rho < 0.70$ $\rightarrow$ System operating efficiently.
- **Moderate Congestion**: $0.70 \le \rho < 0.85$ $\rightarrow$ Optimal counter utilization.
- **High Congestion / SLA Breach Alert**: $\rho \ge 0.85$ or wait time exceeds SLA threshold:
  - Generates automated recommendation:
  - *"Department [Code] is operating at [X]% utilization with [Y] students waiting. Recommend activating [Z] additional counter(s)."*
- Calculated additional counters needed:
  $$c_{\text{target}} = \left\lceil \frac{\lambda}{0.75 \cdot \mu} \right\rceil$$
  $$\Delta c = c_{\text{target}} - c_{\text{active}}$$
